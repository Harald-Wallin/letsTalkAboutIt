<?php

    require_once dirname(__DIR__) . '/src/validation.php';
    require_once dirname(__DIR__) . '/src/auth.php';

    //method = POST
    requirePostRequest();

    session_start();

    //om en användare är inloggad = dess id, annars falsy(tom)
    $userId = requireLoggedIn();

    //validerar en groupId 
    $groupId = requireValidIntInput(INPUT_POST,'group_id');

    $discussionName = trim($_POST['discussion_name'] ?? '');
    $discussionTopic = trim($_POST['discussion_topic'] ?? '');
    $firstPost = trim($_POST['first_post']?? '');

    $errors= [];

    if($discussionName === ''){
        $errors[] = ('A discussion name is required');
    };

    if ($discussionTopic === '') {
        $errors[] = 'A discussion description is required';
    }

    if($firstPost === ''){
        $errors[] = ('A first post is required - write something intresting!');
    };

    if (!empty($errors)) {
    redirectWithErrors(
        $errors,
        '/group.php?id=' .$groupId. '#errorModal'
    );
}

    require_once dirname (__DIR__).'/src/db.php';

    //validerar en group faktiskt finns (Hämtar egentligen all gruppdata, se kommentar i Auth.php)
    requireExistingGroup($pdo, $groupId);


    requireGroupMember($pdo,$userId,$groupId);


    //create discussion + first post
    try{

        $pdo -> beginTransaction();

        $discussionStmt = $pdo->prepare(
            'INSERT INTO discussions(
            discussion_name,
            discussion_topic,
            group_id,
            creator_user_id)

            VALUES (
            :discussion_name,
            :discussion_topic,
            :group_id,
            :creator_user_id)
            
            RETURNING id'
        );


        $discussionStmt -> execute([
            'discussion_name' => $discussionName,
            'discussion_topic' => $discussionTopic,
            'group_id' => $groupId,
            'creator_user_id' => $userId
        ]);

        $discussion = $discussionStmt -> fetch(PDO::FETCH_ASSOC);
        $discussionId = $discussion['id'];

        $firstPostStmt = $pdo -> prepare(
            'INSERT INTO comments(
            creator_user_id,
            discussion_id,
            content)
            VALUES(
            :creator_user_id,
            :discussion_id,
            :content
            )'

        );

        $firstPostStmt -> execute([
            'creator_user_id' => $userId,
            'discussion_id' => $discussionId,
            'content' => $firstPost
        ]);

        $pdo -> commit();
        

    }catch(Throwable $e){

        if($pdo -> inTransaction()){
            $pdo -> rollBack();
        }
        
        //die('Could not create discussion');

        redirectWithErrors(
            ['Could not create discussion'],
            '/group.php?id=' . $groupId . '#errorModal'
        );
    };

    header('Location: /group.php?id='.$groupId);
    exit;
?>