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

    //Array för group-applications
    $applications = [];

    //om man är member hämtas applications-datan och resultat stoppas in i $applications-arrayen
    if ($isMember) {
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
    }


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
    <h2>Discussions</h2>
    <p>You are a member of this group</p>

    <h2>Applications</h2>

    <?php //Om det inte finns applications: ?>
    <?php if (empty($applications)): ?>
        <p>There are no appications</p>
    
    <?php // Om det finns applications, körs för varje application: ?>
    <?php else: ?>
        <?php foreach ($applications as $application): ?>

            <article class ="application-card">

                <strong><?php echo htmlspecialchars($application['user_name']) ?></strong>

                <p> <?php //UX: visar firstname + lastname för lite transparency då vi inte förhindrar att
                          // en användare ansöker till samma grupp flera gånger, än.. ?>
                    <?php echo htmlspecialchars($application['firstname']) ?>
                    <?php echo htmlspecialchars($application['lastname']) ?>
                </p>

                <form method ="POST" action="approveApplication.php">
                    <input type = "hidden" name="application_id" value="<?php (int)$application['application_id']?>">
                    <button type="submit">Accept</button>
                </form>

                <form method ="POST" action="declineApplication.php">
                    <input type = "hidden" name="application_id" value="<?php (int)$application['application_id']?>">
                    <button type="submit">Decline</button>
                </form>

            </article>
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