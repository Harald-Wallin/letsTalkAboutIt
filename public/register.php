<?php

    //Filen ska endast ta emot POST-
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        die ('Invalid request');
    }

    //"??" = "om inte finns så.."
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $repeat_password = trim($_POST['repeat_password'] ?? '');


    //Enkel, specifik error-hantering; om fält lämnas tomma
    $errors = [];

if ($firstName === '') {
    $errors[] = 'First name is required.';
}

if ($lastName === '') {
    $errors[] = 'Last name is required.';
}

if ($userName === '') {
    $errors[] = 'Username is required.';
}

//PHP's inbyggda emailvalidering
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email is not valid.';
}

if ($password === '') {
    $errors[] = 'Password is required.';
}

//Om password inte är samma som det repeterade password'et
if ($password !== $repeatPassword) {
    $errors[] = 'Passwords do not match.';
}

//Om det finns fel i error-arrayen > exit
if (!empty($errors)) {
    var_dump($errors);
    exit;
}

//Hashar "password"
$passwordHash = password_hash($password, PASSWORD_DEFAUL);

//statement, och $pdo ->prepare =~ "object.method()" från JS.
//->prepare = 
//":user_name..." = namngivna placeholder-parametrar i SQL

//Variabel kallad "stmt" som vi laddar med ett PDO-statement (prepare)
$stmt = $pdo->prepare(
    'INSERT INTE users (
    user_name,
    first_name,
    last_name,
    email,
    password_hash)

    VALUES (
    :user_name,
    :first_name,
    :last_name,
    :email,
    :password_hash)'
);

//Exekverar den laddade statementen, stoppar in användarinputen
//FÖRHINDRAR INJECTIONS, genom att användarinputen inte direkt stoppas in 
// i queryn- den går först genom valideringen, SEN sätts den 
$stmt -> execute([
    'user_name' => $userName,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'password_hash' => $passwordHash
]);

echo 'User created';
