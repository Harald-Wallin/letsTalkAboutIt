<?php

//OBS att det inte finns någon kod som förhindrar framtida re-apply's här. Hade jag haft mer tid hade
//jag nog utökat strukturen och funktionaliteten ytterligare.

require_once dirname(__DIR__) . '/src/validation.php';
require_once dirname(__DIR__) . '/src/auth.php';

//method = POST
requirePostRequest();

session_start();

//om en användare är inloggad = dess id, annars falsy(tom)
$userId = requireLoggedIn();

$applicationId = requireValidIntInput(INPUT_POST,'application_id');

require_once dirname(__DIR__) .'/src/db.php';

$application= requireApplication($pdo, $applicationId);

requireGroupMember($pdo, $userId, (int)$application['group_id']);


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