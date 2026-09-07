<?php

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        die ('Invalid request');
    }

    session_start();


    $groupName = trim($_POST['group_name'] ?? '');
    //ingen trim här då beskrivningen får se ut hur användaren än vill att den ska se ut
    $groupDescription = ($_POST['group_description'] ?? '');

    $errors= [];

    if($groupName === ''){
        $errors[] = ('A group name is required');
    };

    //Tänkte först lägga till en check för en VALID description också, men det känns
    //överkurs för mig just nu
    if($groupDescription === ''){
        $errors[] = ('A group description is required');
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

    if ($groupNameStmt -> fetch ()){
        $errors[]= 'Groupname already exists. Maybe you should check '$groupName' out?';
    }


    $stmt = $pdo->prepare(
        'INSERT INTO groups (
        group_name,
        group_description

        VALUES (
        :group_name,
        :group_description'
    );


    $stmt -> execute([
        'group_name' => $groupName,
        'group_description' => $groupDescription
    ]);

    echo $groupName 'sucessfully created- go start a discussion!';

?>