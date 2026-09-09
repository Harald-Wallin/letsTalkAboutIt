<?php

require_once dirname(__DIR__) . '/src/validation.php';
require_once dirname(__DIR__) . '/src/auth.php';

session_start();

//kontrollerar att användaren är inloggad och hämtar dess id
$userId = requireLoggedIn();

//validerar en discussion-id
$discussionId = requireValidIntInput(INPUT_GET, 'id');

require_once dirname(__DIR__) . '/src/db.php';


//Checkar och hämtar discussion
$discussionStmt = $pdo->prepare(
    'SELECT
        discussions.id AS discussion_id,
        discussions.discussion_name,
        discussions.discussion_topic,
        discussions.group_id,
        discussions.creator_user_id,
        discussions.created_at,
        users.user_name
    FROM discussions
    JOIN users
    ON discussions.creator_user_id = users.id
    WHERE discussions.id = :discussion_id'
);

$discussionStmt->execute([
    'discussion_id' => $discussionId
]);

$discussion = $discussionStmt->fetch(PDO::FETCH_ASSOC);

if (!$discussion) {
    http_response_code(404);
    die('Discussion not found');
}


//checkar om user är medlem i gruppen
requireGroupMember(
    $pdo,
    $userId,
    (int)$discussion['group_id']
);


//Hämtar alla posts/comments i discussionen
$commentsStmt = $pdo->prepare(
    'SELECT
        comments.id AS comment_id,
        comments.content,
        comments.created_at,
        users.user_name
    FROM comments
    JOIN users
    ON comments.creator_user_id = users.id
    WHERE comments.discussion_id = :discussion_id
    ORDER BY comments.created_at ASC, comments.id ASC'
);

$commentsStmt->execute([
    'discussion_id' => $discussionId
]);

$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1><?= htmlspecialchars($discussion['discussion_name']) ?></h1>

<p><?= htmlspecialchars($discussion['discussion_topic']) ?></p>

<p>Started by <?= htmlspecialchars($discussion['user_name']) ?></p>

<br>
<br>
<hr>
<br>

<h2>Posts</h2>

<?php if (empty($comments)): ?>

    <p>No posts found</p>

<?php else: ?>

    <?php //För varje comment renderar vi posten ?>
    <?php foreach ($comments as $comment): ?>

        <article class="comment">

            <strong>
                <?= htmlspecialchars($comment['user_name']) ?>
            </strong>

            <p>
                <?= htmlspecialchars($comment['content']) ?>
            </p>

            <?php //Formaterar tiden i år, månad, dag, timme, minut ?>
            <small>
                <?= date('Y-m-d H:i', strtotime($comment['created_at'])) ?>
            </small>

        </article>

        <hr>
        <br>
        <hr>

    <?php endforeach; ?>

<?php endif; ?>