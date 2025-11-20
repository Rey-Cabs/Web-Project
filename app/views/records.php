<?php
// records.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'records';
        include('templates/sidebar.php'); 
        ?>
        <main class="dashboard">
            <div class="patients-header">
                <div>
                    <h2>Patient Records</h2>
                    <p class="table-subtitle">Comprehensive overview of visits, medications, and progress.</p>
                </div>
                <div class="header-actions">
                    <form action="<?= site_url('/records'); ?>" method="get" class="search-form">
                        <input class="search" name="q" type="text" placeholder="Search records" value="<?= html_escape($search_term ?? ''); ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Age</th>
                            <th>Email</th>
                            <th>Disease</th>
                            <th>Type</th>
                            <th>Medicine</th>
                            <th>Schedule</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="center">Edit</th>
                            <th class="center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($records)): ?>
                        <?php foreach($records as $record): ?>
                        <tr>
                            <td><?= html_escape($record['id']); ?></td>
                            <td><?= html_escape($record['first_name'] . ' ' . $record['last_name']); ?></td>
                            <td><?= html_escape($record['age']); ?></td>
                            <td><?= html_escape($record['email']); ?></td>
                            <td><?= html_escape($record['disease']); ?></td>
                            <td><?= html_escape($record['type']); ?></td>
                            <td><?= html_escape($record['medicine']); ?></td>
                            <td><?= html_escape($record['schedule']); ?></td>
                            <td><?= html_escape($record['duration']); ?></td>
                            <td><?= html_escape($record['status']); ?></td>
                            <td class="center">
                                <a href="<?= site_url('/records/edit/'.$record['id']); ?>" class="edit-btn" title="Edit record">✏️</a>
                            </td>
                            <td class="center">
                                <form action="<?= site_url('/records/delete/'.$record['id']); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this record?');">
                                    <button type="submit" class="delete-btn" title="Remove record">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="empty-state">No records available.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($pagination)) echo $pagination; ?>
            </div>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>
