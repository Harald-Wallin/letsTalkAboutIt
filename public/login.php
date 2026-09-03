<?php 
    if ($_SERVER['REQUEST_METHOD'] !=='POST'){
        die('Invalid request');
    };

    $email = strtolower(trim($_POST['email'] ?? ''));

    //tog bort trim på denna då vissa faktiskt har space med flit i 
    //lösenord
    $password = ($_POST['password'] ?? '');

    //Historik
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


    //GLÖMDE DENNA CHECK FRÅN BÖRJAN. Utan denna, om man försöker logga in
    //med en ogiltig email, blir $user i nästa funktion = false, alltså
    //inte längre en array och vi får deprecated-error!
    if (!$user){
        die('Invalid email or password');
    }

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