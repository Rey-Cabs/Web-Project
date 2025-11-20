<?php
// Verify code entry form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="contact-form-section" style="margin-top:120px; max-width:480px; margin-left:auto; margin-right:auto;">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash"><?php flash_alert(); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('/auth/verify') ?>" method="POST" class="contact-form">
            <h2>Enter verification code</h2>
            <p>A 6-digit verification code was sent to your email. It is valid for 5 minutes.</p>
            <div class="form-group">
                <label for="code">Code</label>
                <input type="text" name="code" id="code" maxlength="6" pattern="\d{6}" placeholder="Enter 6-digit code" required>
            </div>
            <input type="hidden" name="purpose" value="<?= html_escape($purpose ?? '') ?>">
            <button type="submit" class="btn">Verify</button>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
