<?php
//Helper-fil för att slippa repitition av authentication/valideringskod


//Validerar request
//funktion returnerar inget, därför typ: void
function requirePostRequest():void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        die('Invalid request');
    }
};

//Inputvalidering (int)
function requireValidIntInput(int $inputType, string $name,): int{
    $value = filter_input(
        $inputType,
        $name,
        FILTER_VALIDATE_INT
    );

    if (!$value){
        die('Invalid input');
    };

    return $value;
};