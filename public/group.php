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

$isMember = (bool) $membershipStmt->fetch();
?>

<h1><?= htmlspecialchars($group['group_name']) ?></h1>
<h3><?= htmlspecialchars($group['group_description']) ?></h3>

<?php if ($isMember): ?>

    <h2>Discussions</h2>

    <p>You are a member of this group</p>

<?php else: ?>

    <p>You are not a member of this group</p>

    <form method="POST" action="applyToGroup.php">
        <input type="hidden" name="group_id" value="<?= (int)$group['id'] ?>">

        <button type="submit">Apply to join</button>
    </form>

<?php endif; ?>