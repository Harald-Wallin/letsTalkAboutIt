<?php

    require_once dirname(__DIR__) . '/src/validation.php';
    require_once dirname(__DIR__) . '/src/auth.php';

    session_start();

    //error flash import
    require_once dirname(__DIR__) . '/src/flash.php';
    $flashErrors = getFlashErrors();

    //om en användare är inloggad = dess id, annars falsy(tom)
    $userId = requireLoggedIn();

    //group.php kräver redan inloggning, header vet då att användare = inloggad
    $isLoggedIn = true;

    //Gammal
    /*Input_get= data kommer från URL/query-string. FILTER... = validera att värden kan
        representera en int

        $groupId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );
    */

    $groupId = requireValidIntInput(INPUT_GET, 'id'); 

    //"skapar" pdo
    require_once dirname(__DIR__) . '/src/db.php';

    $group = requireExistingGroup($pdo,$groupId);

    //Gammal
    /*int kan vara giltig men gruppen kan fortfarande saknas
        if (!$group){

            http_response_code(404);
            die('Group not found.');
        };
    */


    $isMember = isGroupMember($pdo,$userId,$groupId);

    //Arrays för applications + discussions
    $applications = [];
    $discussions = [];

    //om man är member hämtas diskussioner och eventuella ansökningar
    if ($isMember) {

     //Discussions (queryn görs här för att en användare som inte är medlem behöver ingen av denna data alls)  
        $discussionsStmt = $pdo -> prepare(
            'SELECT discussions.id AS discussion_id,
            discussions.discussion_name,
            discussions.discussion_topic,
            discussions.created_at,
            users.user_name
            FROM discussions
            JOIN users
            ON discussions.creator_user_id = users.id
            WHERE discussions.group_id = :group_id
            ORDER BY discussions.created_at DESC'
        );

        $discussionsStmt -> execute([
            'group_id' => $groupId
        ]);

        $discussions = $discussionsStmt -> fetchAll(PDO::FETCH_ASSOC);



        //Applications
        $applicationsStmt = $pdo->prepare(
            'SELECT
            applications.id AS application_id,
            users.id AS user_id,
            users.user_name,
            users.first_name,
            users.last_name
            FROM applications
            JOIN users
            ON applications.user_id = users.id
            WHERE applications.group_id = :group_id
            ORDER BY applications.created_at ASC' //sorterar enligt ascending
        );

        $applicationsStmt->execute([
            'group_id' => $groupId
        ]);

        $applications = $applicationsStmt->fetchAll(PDO::FETCH_ASSOC);
    };


    //Kollar om en application redan finns med user_id + group_id
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

    $hasApplication = (bool) $applicationStmt->fetch();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($group['group_name']) ?> - Let's Talk About It</title>

    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <?php require dirname(__DIR__) . '/src/components/header.php'; ?>
    
    <?php //error flash?>
    <?php require dirname(__DIR__) . '/src/components/errorModal.php'; ?>

    <main>

        <section class="group-header">
            <h1><?= htmlspecialchars($group['group_name']) ?></h1>
            <p class="group-description"><?= htmlspecialchars($group['group_description'])?></p>
        </section>

        <?php //Om användaren är medlem.. ?>
        <?php if ($isMember): ?>

            <?php //.. visas diskutioner + applications ?>
            <section class="group-section">
                <div class="section-heading">
                    <h2>Discussions</h2>
                    <p>Join an existing conversation or start a new one!</p>
                </div>
            
                <?php if(empty ($discussions)): ?>

                    <h3 class="empty-message">This group has no discussions yet. Go create one!</h3>

                <?php else: ?>

                    <div class="discussion-list">

                        <?php //För varje discussion, rendera ett discussionCard?>
                        <?php foreach ($discussions as $discussion): ?>

                            <?php require dirname(__DIR__) . '/src/components/discussionCard.php'; ?>

                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                <?php //create-discussion ?>
                <div class="create-discussion">

                    <h2> Start a discussion!</h2>

                    <form method="POST" action="createDiscussion.php">
                        
                        <input type ="hidden" name="group_id" value="<?= (int)$group['id']?>">

                        <input type ="text" id="discussion_name" name="discussion_name" placeholder="Discussion name" required >

                        <textarea id="discussion_topic" name="discussion_topic" placeholder="Discussion topic" required></textarea>

                        <?php //Första inlägget här- bestäm formula för första: ska vara vanlig kommentar eller extra textarea? ?>
                        <textarea id="first_post" name="first_post" placeholder="First post: What's on your mind?"required></textarea>

                        <button type="submit">Create Discussion</button>
                    </form>
                </div>
            </section>
        
            <section class="group-section">

                <h3>Applications</h3>

                <?php //Om det inte finns applications: ?>
                <?php if (empty($applications)): ?>

                    <p class="empty-message">There are currently no applications to this group</p>
                
                <?php // Om det finns applications, körs för varje application: ?>
                <?php else: ?>
                    <div class="application-list">
                        <?php foreach ($applications as $application): ?>

                            <?php require dirname (__DIR__).'/src/components/applicationCard.php'; ?>

                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <?php elseif ($hasApplication): ?>

                <div class="membership-panel">

                    <h2>Application Pending</h2>
                    <p>You already have an active application towards this group</p>
                </div>

            <?php else: ?>

                <div class="membership-panel">

                    <h2>Join this group!</h2>
                    <p>You are currently not a member of this group</p>

                    <form method="POST" action="applyToGroup.php">
                        <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">

                        <button type="submit">Apply to join</button>
                    </form>
                </div>

            <?php endif; ?>
        </main>
    </body>
    </html>