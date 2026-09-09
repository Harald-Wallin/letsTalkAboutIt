<?php

require_once dirname(__DIR__) . '/src/validation.php';
require_once dirname(__DIR__) . '/src/auth.php';

//Method = POST
requirePostRequest();

//var_dump($_POST);
//exit;

session_start();

//om en användare är inloggad = dess id, annars falsy(tom)
$userId = requireLoggedIn();

$applicationId = requireValidIntInput(INPUT_POST,'application_id');


require_once dirname(__DIR__) . '/src/db.php';


// checkar och hämtar ansökan
$application = requireApplication($pdo, $applicationId);


requireGroupMember($pdo, $userId, (int)$application['group_id']);


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