<?php include 'views/includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo ASSETS_PATH . 'css/login.css'; ?>">
<div class="login-wrapper">
    <div class="login-card">

        <!-- ICON -->
        <div class="user-icon">
            <span>👤</span>
        </div>

        <h2>Create Account</h2>
        <p class="subtitle">Join our emergency response community</p>

        <!-- TABS -->
        <div class="tabs">
            <a class="tab" href="index.php?action=login">Login</a>
            <a class="tab active">Sign Up</a>
        </div>

        <!-- FORM -->
        <form method="POST" action="">

            <label>Full Name<span>*</span></label>
            <div class="input-group">
                <span class="icon">👤</span>
                <input type="text" name="fullname" required>
            </div>

            <label>Phone Number<span>*</span></label>
            <div class="input-group">
                <span class="icon">📞</span>
                <input type="tel" name="phone" required>
            </div>
            <small class="hint">Used for emergency notification</small>

            <label>Date of Birth<span>*</span></label>
            <div class="input-group">
                <span class="icon">📅</span>
                <input type="date" name="dob" required>
            </div>

            <label>Address<span>*</span></label>
            <div class="input-group">
                <span class="icon">🏠</span>
                <input type="text" name="address" required>
            </div>

            <label>Email Address<span>*</span></label>
            <div class="input-group">
                <span class="icon">✉️</span>
                <input type="email" name="email" required>
            </div>

            <label>Password<span>*</span></label>
            <div class="input-group">
                <span class="icon">🔒</span>
                <input type="password" name="password" minlength="8" required>
            </div>
            <small class="hint">Must be at least 8 characters</small>

            <label>Confirm Password<span>*</span></label>
            <div class="input-group">
                <span class="icon">🔒</span>
                <input type="password" name="confirm_password" required>
            </div>

            <div class="terms">
                <input type="checkbox" required>
                <span>
                    I agree to the
                    <a href="#">terms and Conditions</a> and
                    <a href="#">Privacy Policy</a>
                </span>
            </div>

            <button type="submit" class="login-btn">
                Create My Account
            </button>

        </form>

        <div class="divider"></div>

        <p class="signup-text">
            Already have an account?
            <a href="index.php?action=login">Login here</a>
        </p>

    </div>
</div>


<?php include 'views/includes/footer.php'; ?>

</body>
</html>