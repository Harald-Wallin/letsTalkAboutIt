<?php

    require_once dirname(__DIR__) .'/src/validation.php';

    //method = POST
    requirePostRequest();

    //"??" = "om inte finns så.."
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $userName = trim($_POST['user_name'] ?? '');
    //sätter email till lowercase, förhindrar eventuellt olika capitalization-registers
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = ($_POST['password'] ?? '');
    $repeat_password = ($_POST['repeat_password'] ?? '');


    //Enkel, specifik error-hantering;
    $errors = [];

if ($firstName === '') {
    $errors[] = 'First name is required';
}

if ($lastName === '') {
    $errors[] = 'Last name is required';
}

if ($userName === '') {
    $errors[] = 'Username is required';
}

//PHP's inbyggda emailvalidering
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email is not valid';
}

if ($password === '') {
    $errors[] = 'Password is required';
}

//Om password inte är samma som det repeterade password'et
if ($password !== $repeat_password) {
    $errors[] = 'Passwords do not match';
}


require_once dirname (__DIR__).'/src/db.php';

//Om användarnamnet redan är taget..
$userNameStmt = $pdo -> prepare(
    'SELECT id 
    FROM users
    WHERE user_name = :user_name'
);

$userNameStmt -> execute([
    'user_name' => $userName
]);

if ($userNameStmt -> fetch ()){
    $errors[]= 'Username is already taken';
}

//om Email redan är taget...
$emailStmt = $pdo -> prepare(
    'SELECT id 
    FROM users
    WHERE email = :email'
);

$emailStmt -> execute([
    'email' => $email
]);

if ($emailStmt -> fetch ()){
    $errors[]= 'Email is already registered';
};

//Om det finns fel i error-arrayen > exit
if (!empty($errors)) {
    var_dump($errors);
    exit;
}

//Hashar "password"
$passwordHash = password_hash($password, PASSWORD_DEFAULT);


//statement, och $pdo ->prepare =~ "object.method()" från JS.
//->prepare = 
//":user_name..." = namngivna placeholder-parametrar i SQL

//Variabel kallad "stmt" som vi laddar med ett PDO-statement (prepare)
$stmt = $pdo->prepare(
    'INSERT INTO users (
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
// i queryn
$stmt -> execute([
    'user_name' => $userName,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'email' => $email,
    'password_hash' => $passwordHash
]);

echo 'User created';
