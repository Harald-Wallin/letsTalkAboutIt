<?php

//request-check 
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die('Invalid request');
};

session_start();

//inlogg-check
if (!isset($_SESSION['user_id'])){
    die('Log in to apply to a group');
};

//INT-check
$groupId = filter_input(
    INPUT_POST,
    'group_id',
    FILTER_VALIDATE_INT
);

//om felaktigt gruppID
if (!$groupId) {
    die('Invalid group.');
};

require_once dirname(__DIR__) . '/src/db.php';


//om grupp existerar
$groupStmt = $pdo->prepare(
    'SELECT id
     FROM groups
     WHERE id = :group_id'
);

$groupStmt->execute([
    'group_id' => $groupId
]);

if (!$groupStmt->fetch()) {
    die('Group not found.');
};


//kolla om redan medlem
$membershipStmt = $pdo->prepare(
    'SELECT id
    FROM users_groups
    WHERE user_id = :user_id
    AND group_id = :group_id'
);

$membershipStmt->execute([
    'user_id' => $_SESSION['user_id'],
    'group_id' => $groupId
]);

if ($membershipStmt->fetch()) {
    die('You are already a member of this group.');
}


//kolla om ansökan REDAN EXISTERAR
$applicationStmt = $pdo->prepare(
    'SELECT id
    FROM applications
    WHERE user_id = :user_id
    AND group_id = :group_id'
);

$applicationStmt->execute([
    'user_id' => $_SESSION['user_id'],
    'group_id' => $groupId
]);

if ($applicationStmt->fetch()) {
    die('You have already applied to this group');
}


//skapa application
$insertStmt = $pdo->prepare(
    'INSERT INTO applications (
        user_id,
        group_id
     )
     VALUES (
        :user_id,
        :group_id
     )'
);

$insertStmt->execute([
    'user_id' => $_SESSION['user_id'],
    'group_id' => $groupId
]);

header('Location: /group.php?id=' . $groupId);
exit;