<?php 
    $dbHost = getenv('DB_HOST');
    $dbPort = getenv('DB_PORT');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbdbPassword = getenv('DB_PASSWORD');

    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbName=$dbName";

    //PDO = PHP Data Objects
    
    try { 
        $pdo = new PDO($dsn, $dbUser, $dbPassword);

        //              "Hur ska error hanteras?"::"Som exceptions"
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION)

        //PDOExeption = inbyggd error/exeption i PDO, $e = info om error
    }catch (PDOExeption $e){
        die("Database connection failed");
    }; 


?>
