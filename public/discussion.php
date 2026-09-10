<?php

require_once dirname(__DIR__) . '/src/validation.php';
require_once dirname(__DIR__) . '/src/auth.php';

session_start();

    //error flash import
    require_once dirname(__DIR__) . '/src/flash.php';
    $flashErrors = getFlashErrors();

//kontrollerar att användaren är inloggad och hämtar dess id
$userId = requireLoggedIn();

$isLoggedIn= true;

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


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($discussion['discussion_name']) ?> - Let's Talk About It</title>

    <link rel="stylesheet" href="/style.css">
</head>

<body>

    <?php require dirname(__DIR__) . '/src/components/header.php'; ?>

    <?php //error flash?>
    <?php require dirname(__DIR__) . '/src/components/errorModal.php'; ?>

    <main>
        <section class="discussion-header">

            <h1><?= htmlspecialchars($discussion['discussion_name']) ?></h1>

            <p class="discussion-topic"><?= htmlspecialchars($discussion['discussion_topic']) ?></p>

            <p class="discussion-author">Started by <?= htmlspecialchars($discussion['user_name']) ?></p>
        </section>

        <section class="discussion-posts">

            <h2>Posts</h2>

            <?php if (empty($comments)): ?>

                <h3 class="empty-message">No posts found</h3>

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

                <?php endforeach; ?>

            <?php endif; ?>
        </section>

        <section class="reply-section">

            <h3>Reply</h3>

            <form method="POST" action="createComment.php">

                <input type="hidden" name="discussion_id" value="<?= (int)$discussion['discussion_id'] ?>">

                <textarea id="content" name="content" placeholder="Write your reply.." required></textarea>

                <button type="submit">Post reply</button>
            </form>
        </section>
    </main>
</body>
</html>