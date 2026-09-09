<?php

    session_start();

    if (!isset($_SESSION['user_id'])) {
        die('You must be logged in to view a group.');
    }

    //Input_get= data kommer från URL/query-string. FILTER... = validera att värden kan
    //representera en int
    $groupId = filter_input(
        INPUT_GET,
        'id',
        FILTER_VALIDATE_INT
    );

    if (!$groupId) {
        die('Invalid group.');
    };

    require_once dirname(__DIR__) . '/src/db.php';

    $groupStmt = $pdo->prepare(
        'SELECT id,
            group_name,
            group_description,
            creator_user_id,
            created_at
        FROM groups
        WHERE id = :id'
    );

    $groupStmt->execute([
        'id' => $groupId
    ]);

    $group = $groupStmt->fetch(PDO::FETCH_ASSOC);

    //int kan vara giltig men gruppen kan fortfarande saknas
    if (!$group){

        http_response_code(404);
        die('Group not found.');
    };

    //MEMBER
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

    //member = true/false
    $isMember = (bool) $membershipStmt->fetch();

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
        'user_id' => $_SESSION['user_id'],
        'group_id' => $groupId
    ]);

    $hasApplication = (bool) $applicationStmt->fetch();
?>

<?php //Huvud-sidorendering ?>
<h1><?= htmlspecialchars($group['group_name']) ?></h1>
<h3><?= htmlspecialchars($group['group_description']) ?></h3>

<?php //Om användaren är medlem.. ?>
<?php if ($isMember): ?>

    <?php //.. visas diskutioner + applications ?>

    <p>You are a member of this group</p>
    <h2>Discussions</h2>
    
    <?php if(empty ($discussions)): ?>

        <h3>This group has no discussions yet. Go create one! </h3>

    <?php else: ?>

        <?php //För varje discussion, rendera ett discussionCard?>
        <?php foreach ($discussions as $discussion): ?>

            <?php require dirname(__DIR__) . '/src/components/discussionCard.php'; ?>

        <?php endforeach; ?>

    <?php endif; ?>

    <?php //create-discussion ?>
    <form method="POST" action="createDiscussion.php">
        
        <input type ="hidden" name="group_id" value="<?= (int)$group['id']?>">

        <label for="discussion_name">Discussion Name</label>
        <input type ="text" id="discussion_name" name="discussion_name" required >

        <br><br>

        <label for="discussion_topic">Discussion Topic</label>
        <textarea id="discussion_topic" name="discussion_topic" required></textarea>

        <br><br>

        <label for="first_post">First Post</label>
        <textarea id="first_post" name="first_post" required></textarea>
        
        <br><br>

        <?php //Första inlägget här- bestäm formula för första: ska vara vanlig kommentar eller extra textarea? ?>

        <button type="submit">Create Discussion</button>
    </form>
    
        

    

    <h2>Applications</h2>

    <?php //Om det inte finns applications: ?>
    <?php if (empty($applications)): ?>
        <p>There are currently no applications to this group</p>
    
    <?php // Om det finns applications, körs för varje application: ?>
    <?php else: ?>
        <?php foreach ($applications as $application): ?>

            <?php require dirname (__DIR__).'/src/components/applicationCard.php'; ?>

        <?php endforeach; ?>
    <?php endif; ?>

<?php elseif ($hasApplication): ?>

    <p>You already have an active application towards this group</p>

<?php else: ?>

    <p>You are not a member of this group</p>

    <form method="POST" action="applyToGroup.php">
        <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">

        <button type="submit">Apply to join</button>
    </form>

<?php endif; ?>