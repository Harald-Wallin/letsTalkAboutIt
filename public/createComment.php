<?php

require_once dirname(__DIR__) . '/src/validation.php';
require_once dirname(__DIR__) . '/src/auth.php';
require_once dirname(__DIR__) . '/src/flash.php';

//Request = POST
requirePostRequest();

session_start();

//hämtar inloggad användares ID
$userId = requireLoggedIn();

//Validerar discussion_id från formuläret
$discussionId = requireValidIntInput(INPUT_POST, 'discussion_id');

//Hämtar och trimmar kommentaren
$content = trim($_POST['content'] ?? '');

if ($content === '') {
    redirectWithErrors(
        ['A comment is required'],
        '/discussion.php?id=' .$discussionId .'#errorModal'
    );
};

require_once dirname(__DIR__) .'/src/db.php';


// hämtar discussionen från databasen (här litas INTE på group_id från browsern)
$discussionStmt = $pdo->prepare(
    'SELECT
        id,
        group_id
    FROM discussions
    WHERE id = :discussion_id'
);

$discussionStmt->execute([
    'discussion_id' => $discussionId
]);

$discussion = $discussionStmt->fetch(PDO::FETCH_ASSOC);

// Discussionen måste faktiskt finnas
if (!$discussion) {
    http_response_code(404);
    die('Discussion not found');
};


// Kontrollerar medlemskap utifrån group_id som kom från databasen
requireGroupMember(
    $pdo,
    $userId,
    (int)$discussion['group_id']
);


// Skapar kommentaren
$commentStmt = $pdo->prepare(
    'INSERT INTO comments (
        creator_user_id,
        discussion_id,
        content
    )
    VALUES (
        :creator_user_id,
        :discussion_id,
        :content
    )'
);

$commentStmt->execute([
    'creator_user_id' => $userId,
    'discussion_id' => $discussionId,
    'content' => $content
]);


//Tillbaka till discussion
header('Location: /discussion.php?id=' . $discussionId);
exit;