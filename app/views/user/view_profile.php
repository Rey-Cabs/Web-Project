<?php
// view_profile.php - Display user profile information
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #b73b2f;
            padding-bottom: 20px;
        }
        .profile-header h1 {
            color: #b73b2f;
            margin: 0 0 10px 0;
        }
        .profile-section {
            margin: 20px 0;
        }
        .profile-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .profile-value {
            color: #666;
            padding: 10px;
            background: white;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
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
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="about-hero" style="margin-top: 120px;">
        <h1>Your Profile</h1>
        <p>View and manage your account information</p>
    </section>

    <section class="profile-container">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash">
                <?php flash_alert(); ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <h1><?= htmlspecialchars($user['first_name'] ?? '') . ' ' . htmlspecialchars($user['last_name'] ?? ''); ?></h1>
            <p style="color: #666; margin: 0;">@<?= htmlspecialchars($user['email'] ?? ''); ?></p>
        </div>

        <div class="profile-section">
            <div class="profile-label">Email Address</div>
            <div class="profile-value"><?= htmlspecialchars($user['email'] ?? ''); ?></div>

            <div class="profile-label">First Name</div>
            <div class="profile-value"><?= htmlspecialchars($user['first_name'] ?? ''); ?></div>

            <div class="profile-label">Last Name</div>
            <div class="profile-value"><?= htmlspecialchars($user['last_name'] ?? ''); ?></div>

            <div class="profile-label">Age</div>
            <div class="profile-value"><?= htmlspecialchars($user['age'] ?? 'N/A'); ?></div>

            <div class="profile-label">Address</div>
            <div class="profile-value"><?= htmlspecialchars($user['address'] ?? 'Not provided'); ?></div>

            <div class="profile-label">Account Created</div>
            <div class="profile-value"><?= date('F d, Y', strtotime($user['created_at'] ?? '')); ?></div>

            <div class="profile-label">Last Updated</div>
            <div class="profile-value"><?= date('F d, Y H:i A', strtotime($user['updated_at'] ?? '')); ?></div>

            <div class="profile-label">Account Status</div>
            <div class="profile-value">
                <span style="background: #d4edda; padding: 5px 10px; border-radius: 4px; color: #155724;">
                    <?= ucfirst($user['role'] ?? 'user'); ?>
                </span>
            </div>
        </div>

        <div class="profile-actions">
            <?php if (empty($is_admin_viewing)): ?>
                <a href="<?= site_url('/profile/edit'); ?>" class="btn btn-primary">Edit Profile</a>
                <a href="<?= site_url('/dashboard'); ?>" class="btn btn-secondary">Back to Dashboard</a>
            <?php else: ?>
                <a href="<?= site_url('/admin/users'); ?>" class="btn btn-secondary">Back to Users List</a>
            <?php endif; ?>
        </div>
    </section>

    <?php include('templates/footer.php'); ?>
</body>
</html>
