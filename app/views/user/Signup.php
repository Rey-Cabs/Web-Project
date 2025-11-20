<?php
// signup.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>

    <?php include('templates/header.php'); ?>

    <!-- SIGNUP HERO -->
    <section class="about-hero" style="margin-top: 120px;">
        <h1>Create Your Account</h1>
        <p>Join HealthSync today to book appointments and manage your health easily.</p>
    </section>

    <!-- SIGNUP FORM -->
    <section class="contact-form-section">
        <form action="<?= site_url('/auth/signup') ?>" method="POST" class="contact-form">
            <?php if (function_exists('flash_alert')): ?>
                <div class="form-flash">
                    <?php flash_alert(); ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" name="first_name" id="first_name" placeholder="Enter your first name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" id="last_name" placeholder="Enter your last name" required>
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" name="age" id="age" placeholder="Enter your age" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" name="address" id="address" placeholder="Enter your address">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Create a password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" required>
            </div>
            <!-- role removed: signups default to 'user' -->

            <button type="submit" class="btn">Sign Up</button>

            <p style="text-align:center; margin-top:15px;">
                Already have an account? <a href="<?= site_url('/auth/login') ?>" style="color:#b73b2f;">Log In</a>
            </p>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
