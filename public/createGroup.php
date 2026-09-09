<?php

    require_once dirname(__DIR__) . '/src/validation.php';
    require_once dirname(__DIR__) . '/src/auth.php';

    //method = POST
    requirePostRequest();

    session_start();

    //om en användare är inloggad = dess id, annars falsy(tom)
    $userId = requireLoggedIn();


    $groupName = trim($_POST['group_name'] ?? '');
    //ingen trim här då beskrivningen får se ut hur användaren än vill att den ska se ut
    //---TOG BORT REGELN OVAN DÅ "    " INTE SKA ACCEPTERAS SOM LÖSEN---
    $groupDescription = trim($_POST['group_description'] ?? '');

    $errors= [];

    if($groupName === ''){
        $errors[] = ('A group name is required');
    };

    //Tänkte först lägga till en check för en VALID description också, men det känns
    //överkurs för mig just nu
    if($groupDescription === ''){
        $errors[] = ('A group description is required');
    };

    if (!empty($errors)) {
        var_dump($errors);
        exit;
    };

    require_once dirname (__DIR__).'/src/db.php';

    //Om gruppnamnet redan är taget-
    $groupNameStmt = $pdo -> prepare(
        'SELECT id 
        FROM groups
        WHERE group_name = :group_name'
    );

    $groupNameStmt -> execute([
        'group_name' => $groupName
    ]);

    //Här kan man inserta en Locate till gruppen vars namn redan existerar, senare
    if ($groupNameStmt -> fetch ()){
        $errors[]= 'Group name already exists. Why not check out "'.$groupName.'"?';

    }
    
    if (!empty($errors)) {
        var_dump($errors);
        exit;
    };


    //TRANSACTION för att förhindra halvklara resultat av INSERT, t.ex om "INSERT to groups"
    //lyckas men INSERT to groups_members misslyckas så har vi inte en halvtrasig group.
    //Det säkerställs alltså att bägge INSERTS lyckas innan datan faktiskt sätts in i databasen.
    try{//transaction

        $pdo -> beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO groups (
            group_name,
            group_description,
            creator_user_id)

            VALUES (
            :group_name,
            :group_description,
            :creator_user_id)
            
            RETURNING id'
        );


        $stmt -> execute([
            'group_name' => $groupName,
            'group_description' => $groupDescription,
            'creator_user_id' => $userId
        ]);

        $group = $stmt -> fetch(PDO::FETCH_ASSOC);
        $groupId = $group['id'];



        $membershipStmt = $pdo -> prepare(
            'INSERT INTO users_groups(
                user_id,
                group_id
            )
            VALUES(
                :user_id,
                :group_id
            )'
        );

        $membershipStmt -> execute([
            'user_id' => $_SESSION['user_id'],
            'group_id' => $groupId

        ]);

        $pdo -> commit();
        

    }catch(Throwable $e){//transaction
        $pdo -> rollBack();
        die('Could not create group');
    };

    //echo $groupName .'sucessfully created- go start a discussion!';
    header('Location: /');
    exit;


?>