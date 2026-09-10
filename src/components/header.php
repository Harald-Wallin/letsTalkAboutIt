<header class="site-header">
    <div class="site-header-inner">
        <a class="site-logo" href="/">
            <img src="/images/logo.png" alt="Logo">
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
            <?php else: //"?query string" ?>
                <a class="header-login-button" href="#loginModal">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>