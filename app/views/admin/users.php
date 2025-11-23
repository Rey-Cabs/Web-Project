<?php
// admin/users.php - Display all users with admin controls
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - HealthSync Admin</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #b73b2f;
        }
        .admin-header h1 {
            color: #b73b2f;
            margin: 0;
            font-size: 28px;
        }
        .admin-stats {
            background: linear-gradient(135deg, #f5f0ec 0%, #fafafa 100%);
            padding: 18px 24px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
            color: #333;
            border-left: 4px solid #b73b2f;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .admin-stats strong {
            color: #b73b2f;
            font-size: 18px;
        }
        .users-table-container {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .users-table thead {
            background: linear-gradient(135deg, #b73b2f 0%, #9a2f26 100%);
            color: white;
            border-bottom: 2px solid #7a2318;
        }
        .users-table th {
            padding: 16px 18px;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 13px;
        }
        .users-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        .users-table tbody tr {
            transition: all 0.3s ease;
        }
        .users-table tbody tr:nth-child(odd) {
            background: #fafafa;
        }
        .users-table tbody tr:nth-child(even) {
            background: #fff;
        }
        .users-table tbody tr:hover {
            background: #f5f0ec;
            box-shadow: inset 0 0 10px rgba(183, 59, 47, 0.05);
            border-bottom-color: #b73b2f;
        }
        .users-table tbody tr.admin-row {
            background: linear-gradient(90deg, #fffbf0 0%, #fafafa 100%);
        }
        .users-table tbody tr.admin-row:hover {
            background: linear-gradient(90deg, #fff5e6 0%, #f5f0ec 100%);
        }
        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .role-admin {
            background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%);
            color: #856404;
            border: 1px solid #ffeab3;
        }
        .role-user {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #b1dfbb;
        }
        .action-btn {
            padding: 8px 14px;
            margin: 0 4px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        .btn-view {
            background: #e7f3ff;
            color: #0066cc;
            border: 1px solid #b3d9ff;
        }
        .btn-view:hover {
            background: #b3d9ff;
            color: #004499;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
        }
        .btn-delete {
            background: #ffe7e7;
            color: #cc0000;
            border: 1px solid #ffb3b3;
        }
        .btn-delete:hover {
            background: #ffb3b3;
            color: #990000;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(204, 0, 0, 0.2);
        }
        .btn-delete:disabled {
            background: #f0f0f0;
            color: #999;
            border: 1px solid #ddd;
            cursor: not-allowed;
            opacity: 0.6;
            transform: none;
        }
        .empty-state {
            text-align: center;
            padding: 50px 40px;
            color: #666;
        }
        .empty-state h2 {
            color: #b73b2f;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .empty-state p {
            font-size: 16px;
            color: #999;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            margin: 0 2px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #b73b2f;
        }
        .pagination a:hover {
            background: #f0f0f0;
        }
        .pagination .active {
            background: #b73b2f;
            color: white;
        }
    </style>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <section class="about-hero" style="margin-top: 120px;">
        <h1>User Management</h1>
        <p>View and manage all registered users</p>
    </section>

    <section class="admin-container">
        <?php if (function_exists('flash_alert')): ?>
            <div class="form-flash">
                <?php flash_alert(); ?>
            </div>
        <?php endif; ?>

        <div class="admin-header">
            <h1>All Users</h1>
            <a href="<?= site_url('/dashboard'); ?>" class="action-btn btn-view">Back to Dashboard</a>
        </div>

        <div class="admin-stats">
            Total Users: <strong><?= count($users ?? []); ?></strong>
        </div>

        <?php if (!empty($users)): ?>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Age</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr <?= $user['role'] === 'admin' ? 'class="admin-row"' : ''; ?>>
                                <td><?= htmlspecialchars($user['id']); ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($user['first_name'] ?? ''); ?> 
                                           <?= htmlspecialchars($user['last_name'] ?? ''); ?></strong>
                                </td>
                                <td><?= htmlspecialchars($user['email'] ?? ''); ?></td>
                                <td><?= htmlspecialchars($user['age'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="role-badge <?= $user['role'] === 'admin' ? 'role-admin' : 'role-user'; ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'] ?? 'user')); ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'] ?? '')); ?></td>
                                <td>
                                    <a href="<?= site_url('/profile/view/' . $user['id']); ?>" class="action-btn btn-view">View</a>
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" 
                                              action="<?= site_url('/admin/users/delete/' . $user['id']); ?>" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');
                                                        return false;">
                                            <button type="submit" class="action-btn btn-delete">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="action-btn btn-delete" disabled title="Cannot delete admin users">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>No Users Found</h2>
                <p>There are currently no registered users in the system.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php include('templates/footer.php'); ?>

    <script>
        // Enhanced delete confirmation with form submission
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[action*="/delete/"]');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const userName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                    if (!confirm('Are you sure you want to delete user: ' + userName + '?\n\nThis action cannot be undone.')) {
                        e.preventDefault();
                        return false;
                    }
                    return true;
                });
            });
        });
    </script>
</body>
</html>
