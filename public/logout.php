<?php
    session_start();

    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        die('invalid request');
    };

    //Tömmer sessionens data
    $_SESSION = [];
    
    session_destroy();
    header('Location: /');
    exit;
?>