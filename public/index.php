<?php
    session_start();
    $isLoggedIn = isset($_SESSION['user_id']);
    $user = null;

    var_dump($_SESSION);
    //var_dump($_SERVER);
?>

<?php
    if($isLoggedIn){
        require_once dirname(__DIR__).'/src/db.php';
        
        $stmt = $pdo -> prepare(
            'SELECT id,
            user_name,
            first_name,
            last_name,
            email
            FROM users
            WHERE id = :id'
        );

        $stmt ->execute([
            'id' => $_SESSION['user_id']
        ]);

        $user= $stmt -> fetch(PDO::FETCH_ASSOC);

        ?>
            <h3> Welcome, <?php echo $user['user_name'] ?></h3>
            <p>Browse all the groups freely</p>
        <?php
    } else {
        ?>
            <h2>Login...</h2>
            <form method="POST" action="login.php">
                <label for="login_email">Email</label>
                <input type="email" id="login_email" name="email" required>
                <br><br>

                <label for="login_password">Password</label>
                <input type="password" id="login_password" name="password" required>
                <br><br>

                <button type="submit">Login</button>
            </form>

            <br><br>

            <h2>.. or create a user!</h2>
            <form method="POST" action="register.php">

                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name" required>
                <br><br>

                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name" required>
                <br><br>

                <label for="user_name">Username</label>
                <input type="text" id="user_name" name="user_name" required>
                <br><br>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <br><br>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <br><br>

                <label for="repeat_password">Repeat password</label>
                <input type="password" id="repeat_password" name="repeat_password" required>
                <br><br>

                <button type="submit">Register</button>
            </form>
        <?php
    };
?>  







    