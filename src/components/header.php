<header class="site-header">
    <div class="site-header-inner">
        <a class="site-logo" href="/">
            <img src="../media/header.png">
        </a>
        
        <nav class ="site-nav">
            <a href="/">Home</a>
            <a href="/groups.php">Browse forums</a>
        </nav>

        <div class="header-auth">
            <?php if(!empty($isLoggedIn)): ?>
                <form method="POST" action="/logout.php">
                    <button type="submit">Logout</button>
                </form>
            <?php else: // ?>
                <a class="header-login-button" href="/?auth=login">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>