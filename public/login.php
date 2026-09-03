<?php 
    if ($_SERVER['REQUEST_METHOD'] !=='POST'){
        die('Invalid request');
    };

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    //var_dump ($email, $password);
    //var_dump($_SERVER);
    //var_dump($_POST);

    $errors=[];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors[]= 'Email is not valid';
    };

    if ($password === ''){
        $errors[]= 'Password Required';
    };

    if(!empty ($errors)){
        var_dump($errors);
        exit;
    };

    require_once dirname(__DIR__).'/src/db.php';


    $stmt = $pdo -> prepare(
        'SELECT id, 
        user_name, 
        email,
        password_hash
        FROM users
        WHERE email = :email'
    );

    $stmt -> execute([
        'email' => $email
    ]);

    //                  Hämtas som en assoicative PHP array
    $user = $stmt -> fetch (PDO::FETCH_ASSOC);

    //"password_verify (lösenord som just skickades, hashen som just hämtades)
    if(!password_verify($password, $user['password_hash'])){
        die('Invalid email or password');
    };

    session_start();

    //skydd mot session fixation
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];

    header('Location: /');
    exit;
?>