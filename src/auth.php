<?php
//Helper-fil för att slippa repitition av authentication/valideringskod


//login-checkarna
function requireLoggedIn(): int{

    if (!isset($_SESSION['user_id'])) {
        die('You must be logged in');
    }

    return (int) $_SESSION['user_id'];
};


//Check att grupp existerar
function requireExistingGroup(PDO $pdo, int $groupId): array
{
    $stmt = $pdo->prepare(
        'SELECT
        id,
        group_name,
        group_description,
        creator_user_id,
        created_at
        FROM groups
        WHERE id = :group_id'
    );

    $stmt->execute([
        'group_id' => $groupId
    ]);

    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group){
        http_response_code(404);
        die('Group not found');
    }

    return $group;
};


//Checkar om användaren är medlem i grupp
function isGroupMember(PDO $pdo, int $userId, int $groupId): bool{
    $stmt = $pdo->prepare(
        'SELECT id
        FROM users_groups
        WHERE user_id = :user_id
        AND group_id = :group_id'
    );

    $stmt->execute([
        'user_id' => $userId,
        'group_id' => $groupId
    ]);

    return (bool) $stmt->fetch();
};

//Kontrollerar om användare är medlem i grupp vid event/action/etc
function requireGroupMember(PDO $pdo, int $userId, int $groupId): void{
    if (!isGroupMember($pdo, $userId, $groupId)) {
        die('You are not allowed to do this');
    }
};


//Hämtar en application och spottar antingen ut den eller ger felmeddelande
function requireApplication(PDO $pdo, int $applicationId): array{
    $stmt = $pdo->prepare(
        'SELECT
        id,
        user_id,
        group_id
        FROM applications
        WHERE id = :application_id'
    );

    $stmt->execute([
        'application_id' => $applicationId
    ]);

    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        http_response_code(404);
        die('Application not found');
    }

    return $application;
};