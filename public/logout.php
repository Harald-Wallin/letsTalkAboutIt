<?php
    require_once dirname(__DIR__) . '/src/validation.php';

    //method = POST
    requirePostRequest();

    session_start();

    //Tömmer sessionens data
    $_SESSION = [];
    
    session_destroy();

    header('Location: /');
    exit;
?>