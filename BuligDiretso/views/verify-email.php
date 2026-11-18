<?php include 'views/includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/login.css'; ?>">

<div class="main-container">
    <div class="login-wrapper">
        <div class="login-card">
            <h2>Verify your email</h2>
            <p class="subtitle">Confirm your email address to activate your BuligDiretso account.</p>

            <form action="<?php echo BASE_URL; ?>index.php?action=verify_email" method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($verificationToken, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="login-btn">Verify Email Address</button>
            </form>

            <p class="signup-text">
                <a href="index.php?action=login">Return to login</a>
            </p>
        </div>
    </div>
</div>

<?php include 'views/includes/footer.php'; ?>

</body>
</html>
