<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Record - HealthSync</title>
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
                <h2>Patient Full Record</h2>
                <p class="table-subtitle">Complete history and upcoming schedule for this patient.</p>
            </div>
            <div class="header-actions">
                <a href="<?= site_url('/records'); ?>" class="btn btn-secondary">Back to Records</a>
                <a href="<?= site_url('/records/view/'.($patient['id'] ?? 0).'/export_pdf'); ?>" class="btn-pdf">Download PDF</a>
            </div>
        </div>

        <section class="patient-info-card">
            <h3>Patient Information</h3>
            <div class="info-row"><strong>Name:</strong> <?= html_escape(($patient['first_name'] ?? '').' '.($patient['last_name'] ?? '')); ?></div>
            <div class="info-row"><strong>Age:</strong> <?= html_escape($patient['age'] ?? ''); ?></div>
            <div class="info-row"><strong>Email:</strong> <?= html_escape($patient['email'] ?? ''); ?></div>
            <div class="info-row"><strong>Address:</strong> <?= html_escape($patient['address'] ?? ''); ?></div>
            <div class="info-row"><strong>Disease:</strong> <?= html_escape($patient['disease'] ?? ''); ?></div>
            <div class="info-row"><strong>Type:</strong> <?= html_escape($patient['type'] ?? ''); ?></div>
            <div class="info-row"><strong>Status:</strong> <?= html_escape($patient['status'] ?? ''); ?></div>
        </section>

        <section class="table-container">
            <h3>Upcoming Appointments / Medications</h3>
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>Schedule</th>
                        <th>Type</th>
                        <th>Disease</th>
                        <th>Medicine</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($upcoming)): ?>
                    <?php foreach ($upcoming as $row): ?>
                        <tr>
                            <td><?= html_escape($row['schedule'] ?? '-'); ?></td>
                            <td><?= html_escape($row['type'] ?? '-'); ?></td>
                            <td><?= html_escape($row['disease'] ?? '-'); ?></td>
                            <td><?= html_escape($row['medicine'] ?? '-'); ?></td>
                            <td><?= html_escape($row['duration'] ?? '-'); ?></td>
                            <td><?= html_escape($row['status'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-state">No upcoming appointments or medications.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="table-container">
            <h3>History</h3>
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>Schedule</th>
                        <th>Type</th>
                        <th>Disease</th>
                        <th>Medicine</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= html_escape($row['schedule'] ?? '-'); ?></td>
                            <td><?= html_escape($row['type'] ?? '-'); ?></td>
                            <td><?= html_escape($row['disease'] ?? '-'); ?></td>
                            <td><?= html_escape($row['medicine'] ?? '-'); ?></td>
                            <td><?= html_escape($row['duration'] ?? '-'); ?></td>
                            <td><?= html_escape($row['status'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-state">No past records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<?php include('templates/footer.php'); ?>
<script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>
