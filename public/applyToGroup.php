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


require_once dirname(__DIR__) . '/src/db.php';

//validerar en group faktiskt finns (Hämtar egentligen all gruppdata, se kommentar i Auth.php)
requireExistingGroup($pdo, $groupId);


//kollar om redan medlem
if (isGroupMember($pdo, $userId, $groupId)) {
    redirectWithErrors(
        ['You are already a member of this group'],
        '/group.php?id=' . $groupId . '#errorModal'
    );
}


//kollar om ansökan REDAN EXISTERAR
$applicationStmt = $pdo->prepare(
    'SELECT id
    FROM applications
    WHERE user_id = :user_id
    AND group_id = :group_id'
);

$applicationStmt->execute([
    'user_id' => $userId,
    'group_id' => $groupId
]);

if ($applicationStmt->fetch()) {
    redirectWithErrors(
        ['You have already applied to this group'],
        '/group.php?id=' . $groupId . '#errorModal'
    );
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
    'user_id' => $userId,
    'group_id' => $groupId
]);

header('Location: /group.php?id=' . $groupId);
exit;