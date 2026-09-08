<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

var_dump($_POST);
exit;

session_start();

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in');
}

$applicationId = filter_input(
    INPUT_POST,
    'application_id',
    FILTER_VALIDATE_INT
);

if (!$applicationId) {
    die('Invalid application.');
}

require_once dirname(__DIR__) . '/src/db.php';


// hämtar ansökan
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
};


// kontrollera medlemskap även här
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

//alltså om membershipStmt = false..
if(!$membershipStmt->fetch()) {
    die('You are not allowed to approve this application');
};


//tansaction vid godkännande då det är TVÅ saker som modifieras i databasen: ny rad i users_groups
// och en application tas bort från applications
try{

    $pdo->beginTransaction();

    $insertMembershipStmt = $pdo->prepare(
        'INSERT INTO users_groups(
            user_id,
            group_id
        )
        VALUES(
            :user_id,
            :group_id
        )'
    );

    $insertMembershipStmt->execute([
        'user_id' => $application['user_id'],
        'group_id' => $application['group_id']
    ]);


    $deleteApplicationStmt = $pdo->prepare(
        'DELETE FROM applications
        WHERE id = :application_id'
    );

    $deleteApplicationStmt->execute([
        'application_id' => $applicationId
    ]);

    //transaction avslutas(förhoppningsvis här)
    $pdo->commit();

}catch (Throwable $e) {

    if ($pdo->inTransaction()){
        $pdo->rollBack();
    }

    die('Could not approve application');
};

//Redirect till applications group_id, alltså till tillhörande gruppsida
header('Location: /group.php?id=' .(int)$application['group_id']);

exit;