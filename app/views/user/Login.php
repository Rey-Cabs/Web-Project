<?php
// login.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>

    <?php include('templates/header.php'); ?>

    <!-- LOGIN HERO -->
    <section class="about-hero" style="margin-top: 120px;">
        <h1>Welcome Back</h1>
        <p>Log in to access your HealthSync account and manage your appointments.</p>
    </section>

    <!-- LOGIN FORM -->
    <section class="contact-form-section">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash">
                <?php flash_alert(); ?>
            </div>
        <?php endif; ?>
        <form action="<?= site_url('/auth/login') ?>" method="POST" class="contact-form" autocomplete="off">
            <!-- Dummy fields to prevent browser autofill of credentials -->
            <input type="text" name="prevent_autofill_username" id="prevent_autofill_username" style="position:absolute;left:-9999px;top:-9999px;" autocomplete="username">
            <input type="password" name="prevent_autofill_password" id="prevent_autofill_password" style="position:absolute;left:-9999px;top:-9999px;" autocomplete="current-password">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required autocomplete="off" value="">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="new-password" value="">
            </div>
            <script>
                // Ensure fields are cleared after page load to override aggressive autofill
                (function(){
                    function clearLoginFields(){
                        try {
                            var e = document.getElementById('email');
                            var p = document.getElementById('password');
                            if (e) e.value = '';
                            if (p) p.value = '';
                        } catch(err){}
                    }
                    document.addEventListener('DOMContentLoaded', function(){
                        clearLoginFields();
                        // Some browsers autofill after load, clear again shortly after
                        setTimeout(clearLoginFields, 200);
                    });
                })();
            </script>

            <button type="submit" class="btn">Log In</button>

            <p style="text-align:center; margin-top:15px;">
                Don't have an account? <a href="<?= site_url('/auth/signup') ?>" style="color:#b73b2f;">Sign Up</a>
            </p>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
