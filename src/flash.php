<?php

//Sparar felmeddelanden i sessionen
function setFlashErrors(array $errors): void
{
    $_SESSION['flash_errors'] = $errors;
}


//hämtar felmeddelanden >  tar bort dem från session(därför visas de bara en gång )
function getFlashErrors(): array
{
    $errors = $_SESSION['flash_errors'] ?? [];

    unset($_SESSION['flash_errors']);

    return $errors;
};


//Helper för spara error -> redirect -> exit
//never = funktion kommer aldrig tillbaks till anropare
function redirectWithErrors(array $errors, string $location): never
{
    setFlashErrors($errors);

    header('Location: '. $location);

    exit;
};