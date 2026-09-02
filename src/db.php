<?php 
    $dbHost = getenv('DB_HOST');
    $dbPort = getenv('DB_PORT');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPassword = getenv('DB_PASSWORD');

    $dsn = "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName";

    //PDO = PHP Data Objects- man proppar ner de variabler/värden ovan
    // i ett PDO-objekt som sedan får metoder man kan använda för t.ex 
    //anslutning, hur querys skickas, hur resultat tas emot etc..
    try { 
        $pdo = new PDO($dsn, $dbUser, $dbPassword);

        //              "Hur ska error hanteras?"::"Som exceptions"
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //PDOExeption = inbyggd error/exeption i PDO, $e = info om error
    }catch (PDOExeption $e){
        die("Database connection failed");
    }; 


?>
