<?php
// Forgot password request - ask for email to send verification code
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="contact-form-section" style="margin-top:120px; max-width:480px; margin-left:auto; margin-right:auto;">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash"><?php flash_alert(); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('/auth/forgot') ?>" method="POST" class="contact-form">
            <h2>Forgot Password</h2>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="Enter your account email" required>
            </div>
            <button type="submit" class="btn">Send verification code</button>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
