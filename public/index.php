<?php
    session_start();

    //error flash import
    require_once dirname(__DIR__) . '/src/flash.php';
    $flashErrors = getFlashErrors();


    $isLoggedIn = isset($_SESSION['user_id']);
    $user = null;

    //var_dump($_SESSION);
    //var_dump($_SERVER);

    require_once dirname(__DIR__).'/src/db.php';

    if($isLoggedIn){
        
        //USER
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
    };

    //GROUPS
    $groupsStmt = $pdo->prepare(
        'SELECT id, 
            group_name, 
            group_description
        FROM groups'
    );

    //inga placeholders i queryn, så vi behöver inte lägga till "utökad" kod
    $groupsStmt->execute();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Let's Talk About It</title>

    <link rel="stylesheet" href="/style.css">
</head>

<body>

    <?php //HEADER ?>
    <?php require dirname(__DIR__) . '/src/components/header.php'; ?>
    <?php //error flash?>
    <?php require dirname(__DIR__) . '/src/components/errorModal.php'; ?>

    <main>
        <div class="index-layout">
            <section class="forum-sidebar">
                <div class="sidebar-heading">

                    <h2>Forum Groups</h2>
                    <a href="/groups.php">Browse all</a>
                </div>

                <div class="group-list">

                    <?php $shownGroups = 0; ?>

                    <?php while ($shownGroups < 4 && $group = $groupsStmt->fetch(PDO::FETCH_ASSOC)): ?>

                        <?php 
                            require dirname(__DIR__) . '/src/components/groupCard.php';
                            $shownGroups++;
                        ?>
                    <?php endwhile; ?>

                </div>

            </section>

            <section class="index-hero">

                <?php if ($isLoggedIn && $user): ?>

                    <div class="welcome-panel">
                    
                        <h3> Welcome, <?php echo htmlspecialchars($user['user_name']) ?></h3>
                        <p>What discussion will you join today?</p>

                        <h3>Create a new group</h3>
                        <form method="POST" action="createGroup.php">
                            <label for="group_name">Group Name</label>
                            <input type="text" id="group_name" name="group_name" required>
                                
                            <label for="group_description">Describe your group</label>
                            <textarea id="group_description" name="group_description" required></textarea>

                            <button type="submit">Create group</button> 
                        </form>
                    </div>

                <?php else: ?>
                            
                    <div class="auth-intro">
                        <h1>Find your people</h1>
                        <p>Browse communitites, join discussions and start your own forum!</p>

                        <div class="auth-intro-actions">
                            <?php //"#loginModal": element med id="loginModal" => CSS :target, och visas?>
                            <a class="auth-button" href="#loginModal">Login</a>
                            <a class="auth-button secondary-button" href="#registerModal">Sign up</a>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>
        <?php if (!$isLoggedIn): ?> 

            <div class="auth-modal" id="loginModal">   
                
                <div class="auth-modal-content">
                    
                    <?php //href="#" tar bort #loginModal som target >  modalen döljs?>
                    <a href="#" class="auth-modal-close">X</a>

                    <h2>Login</h2>

                    <?php //hanterar ERRORS?>
                    <?php if (!empty($flashErrors)): ?>

                    <div class="form-errors">

                        <?php foreach ($flashErrors as $error): ?>

                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>

                        
                    <form method="POST" action="login.php">

                        <input type="email" id="login_email" name="email" placeholder="Email" required>

                        <input type="password" id="login_password" name="password" placeholder="Password" required>

                        <button type="submit">Login</button>
                    </form>

                    <p class="auth-switch-text">Not a member yet?<a href="#registerModal">Sign up</a></p>
                </div>
            </div>

            <div class="auth-modal" id="registerModal">

                <div class="auth-modal-content">

                        <a href="#" class="auth-modal-close">X</a>

                        <h2>Create Account</h2>

                        <?php //hanterar ERRORS?>
                        <?php if (!empty($flashErrors)): ?>

                            <div class="form-errors">

                                <?php foreach ($flashErrors as $error): ?>

                                    <p><?= htmlspecialchars($error) ?></p>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                        <form method="POST" action="register.php">

                            <input type="text" id="first_name" name="first_name" placeholder="First Name" required>

                            <input type="text" id="last_name" name="last_name" placeholder="Last Name"required>

                            <input type="text" id="user_name" name="user_name" placeholder="Username (will be shown in your posts)" required>

                            <input type="email" id="register_email" name="email" placeholder="Email@email.com" required>

                            <input type="password" id="register_password" name="password" placeholder="Password" required>

                            <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat Password" required>

                            <button type="submit">Register</button>
                        </form>

                        <p class="auth-switch-text">Already a member?<a href="#loginModal">Login</a></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>

 







    