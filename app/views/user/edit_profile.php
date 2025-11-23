<?php
// edit_profile.php - Edit user profile information
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <style>
        .profile-edit-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .profile-edit-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #b73b2f;
            padding-bottom: 20px;
        }
        .profile-edit-header h1 {
            color: #b73b2f;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #b73b2f;
            box-shadow: 0 0 5px rgba(183, 59, 47, 0.3);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            flex: 1;
            min-width: 150px;
        }
        .btn-primary {
            background: #b73b2f;
            color: white;
        }
        .btn-primary:hover {
            background: #9a2f26;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .password-note {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        .danger-zone {
            margin-top: 40px;
            padding: 20px;
            background: #ffe6e6;
            border: 2px solid #dc3545;
            border-radius: 4px;
        }
        .danger-zone h3 {
            color: #dc3545;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="about-hero" style="margin-top: 120px;">
        <h1>Edit Profile</h1>
        <p>Update your account information</p>
    </section>

    <section class="profile-edit-container">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash">
                <?php flash_alert(); ?>
            </div>
        <?php endif; ?>

        <div class="profile-edit-header">
            <h1>Edit Your Information</h1>
        </div>

        <form action="<?= site_url('/profile/update'); ?>" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="first_name">First Name *</label>
                <input type="text" id="first_name" name="first_name" 
                       value="<?= htmlspecialchars($user['first_name'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name *</label>
                <input type="text" id="last_name" name="last_name" 
                       value="<?= htmlspecialchars($user['last_name'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($user['email'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="0" max="150"
                       value="<?= htmlspecialchars($user['age'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" 
                         ><?= htmlspecialchars($user['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password">
                <div class="password-note">Leave blank to keep your current password. Minimum 6 characters.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?= site_url('/profile'); ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
