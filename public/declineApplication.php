<?php

if ($_SERVER['REQUEST_METHOD'] !=='POST'){
    die('Invalid request');
};

session_start();

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in');
};

$applicationId = filter_input(
    INPUT_POST,
    'application_id',
    FILTER_VALIDATE_INT
);

if (!$applicationId) {
    die('Invalid application');
};

require_once dirname(__DIR__) .'/src/db.php';


//hämtar ansökan..
$applicationStmt = $pdo->prepare(
    'SELECT id,
    user_id,
    group_id
    FROM applications
    WHERE id = :application_id'
);

$applicationStmt->execute([
    'application_id' => $applicationId
]);

$application = $applicationStmt->fetch(PDO::FETCH_ASSOC);

if (!$application) {
    die('Application not found');
}


//kontrollerar medlemskap
$membershipStmt = $pdo->prepare(
    'SELECT id
    FROM users_groups
    WHERE user_id = :user_id
    AND group_id = :group_id'
);

$membershipStmt->execute([
    'user_id' => $_SESSION['user_id'],
    'group_id' => $application['group_id']
]);

if (!$membershipStmt->fetch()) {
    die('You are not allowed to decline this application');
}


//tar bort ansökan
$deleteApplicationStmt = $pdo->prepare(
    'DELETE FROM applications
    WHERE id = :application_id'
);

$deleteApplicationStmt->execute([
    'application_id' => $applicationId
]);


//Redirect tillbaks till gruppsidan
header('Location: /group.php?id=' . (int)$application['group_id']);

exit;