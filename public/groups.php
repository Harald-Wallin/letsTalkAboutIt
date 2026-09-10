<?php

    session_start();

    //errorflashing- jag vill undvika att ett error dirigerar till en helt ny sida
    require_once dirname(__DIR__) . '/src/flash.php';
    $flashErrors = getFlashErrors();

    $isLoggedIn = isset($_SESSION['user_id']);

    require_once dirname(__DIR__) . '/src/db.php';

    $stmt = $pdo->prepare(
        'SELECT
            id,
            group_name,
            group_description
        FROM groups
        ORDER BY created_at DESC'
    );

    $stmt->execute();

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Forums - Let's Talk About It</title>

    <link rel="stylesheet" href="/style.css">
</head>

<body>

    <?php //HEADER?>
    <?php require dirname(__DIR__) . '/src/components/header.php'; ?>

    <?php //error flash?>
    <?php require dirname(__DIR__) . '/src/components/errorModal.php'; ?>

    <main>
        <section class="browse-header">
            <h1>Browse Forums</h1>
            <p>Find a community and join the conversation.</p>
        </section>

        <?php if (empty($groups)): ?>

            <p class="empty-message">There are no forum groups yet.</p>
        <?php else: ?>

            <div class="browse-group-grid">

                <?php foreach ($groups as $group): ?>

                    <?php require dirname(__DIR__) .'/src/components/groupCard.php'; ?>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>