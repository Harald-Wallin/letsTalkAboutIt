<?php

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        die ('Invalid request');
    };

    session_start();

    if (!isset($_SESSION['user_id'])) {
        die('Log in to create a discussion');
    };

    $groupId = filter_input(
        INPUT_POST,
        'group_id',
        FILTER_VALIDATE_INT
    );

    $discussionName = trim($_POST['discussion_name'] ?? '');
    $discussionTopic = trim($_POST['discussion_topic'] ?? '');
    $firstPost = trim($_POST['first_post']?? '');

    $errors= [];

    if(!$groupId){
        $errors[] = ('Invalid group');
    };

    if($discussionName === ''){
        $errors[] = ('A discussion name is required');
    };

    if($discussionTopic === ''){
        $errors[] = ('A discussion description is required');
    };

    if($firstPost === ''){
        $errors[] = ('A first post is required - write something intresting!');
    };

    if (!empty($errors)) {
        var_dump($errors);
        exit;
    };

    require_once dirname (__DIR__).'/src/db.php';

    //kontrollera att grupp existerar
    $groupStmt = $pdo -> prepare(
        'SELECT id 
        FROM groups
        WHERE id = :group_id'
    );

    $groupStmt -> execute([
        'group_id' => $groupId
    ]);

    if (!$groupStmt -> fetch ()){
        die('Group not found');

    };

    //kontrollera membership
    $membershipStmt = $pdo -> prepare(
        'SELECT id
        FROM users_groups
        WHERE user_id = :user_id
        AND group_id = :group_id'
    );

    $membershipStmt -> execute([
        'user_id' => $_SESSION['user_id'],
        'group_id' => $groupId
    ]);

    if(!$membershipStmt -> fetch()){
        die('You are not allowed to create a discussion in this group');
    };


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
            'creator_user_id' => $_SESSION['user_id']
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
            'creator_user_id' => $_SESSION['user_id'],
            'discussion_id' => $discussionId,
            'content' => $firstPost
        ]);

        $pdo -> commit();
        

    }catch(Throwable $e){

        if($pdo -> inTransaction()){
            $pdo -> rollBack();
        }
        
        die('Could not create discussion');
    };

    header('Location: /group.php?id='.$groupId);
    exit;
?>