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
                <p class="table-subtitle">Overview of past and upcoming medications.</p>
            </div>
            <div class="header-actions">
                <form action="<?= site_url('/records'); ?>" method="get" class="search-form">
                    <input class="search" name="q" type="text" placeholder="Search records" value="<?= html_escape($search_term ?? ''); ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <?php if($role === 'admin'): ?>
                    <a href="<?= site_url('/records/export_pdf'); ?>" class="btn-pdf">Download PDF</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if($role === 'admin'): ?><th>Patient</th><?php endif; ?>
                        <th>Age</th>
                        <th>Email</th>
                        <th>Disease</th>
                        <th>Type</th>
                        <th>Medicine</th>
                        <th>Schedule</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($records)): ?>
                    <?php foreach($records as $record): ?>
                        <tr onclick="window.location.href='<?= site_url('/records/view/'.($record['patient_id'] ?? 0)); ?>';" style="cursor: pointer;">
                            <td><?= html_escape($record['id'] ?? 'None'); ?></td>
                            <?php if($role === 'admin'): ?>
                                <td><?= html_escape(($record['first_name'] ?? 'None') . ' ' . ($record['last_name'] ?? '')); ?></td>
                            <?php endif; ?>
                            <td><?= html_escape($record['age'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['email'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['disease'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['type'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['medicine'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['schedule'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['duration'] ?? 'None'); ?></td>
                            <td><?= html_escape($record['status'] ?? 'Unknown'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $role==='admin'?10:8 ?>" class="empty-state">No records available.</td>
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
