<?php
// Reset password form (after successful verification)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="contact-form-section" style="margin-top:120px; max-width:480px; margin-left:auto; margin-right:auto;">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash"><?php flash_alert(); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('/auth/reset_password') ?>" method="POST" class="contact-form">
            <h2>Set a New Password</h2>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" name="password" id="password" placeholder="Enter new password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required>
            </div>
            <button type="submit" class="btn">Update Password</button>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
