<?php
// appointments.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'appointments';
        include('templates/sidebar.php'); 
        ?>

        <main class="dashboard">
            <div class="patients-header">
                <div>
                    <h2>Appointments</h2>
                    <p class="table-subtitle">Monitor scheduled visits and follow-up sessions.</p>
                </div>
                <div class="header-actions">
                    <form action="<?= site_url('/appointments'); ?>" method="get" class="search-form">
                        <input class="search" name="q" type="text" placeholder="Search appointments" value="<?= html_escape($search_term ?? ''); ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                    <a href="<?= site_url('/appointments/create'); ?>" class="btn btn-primary">+ Schedule Appointment</a>
                </div>
            </div>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Disease</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="center">Edit</th>
                            <th class="center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($appointments)): ?>
                        <?php foreach($appointments as $appointment): ?>
                        <tr>
                            <td><?= html_escape($appointment['id']); ?></td>
                            <td><?= html_escape($appointment['first_name']); ?></td>
                            <td><?= html_escape($appointment['last_name']); ?></td>
                            <td><?= html_escape($appointment['email']); ?></td>
                            <td><?= html_escape($appointment['disease']); ?></td>
                            <td><?= html_escape($appointment['type']); ?></td>
                            <td><?= html_escape($appointment['schedule']); ?></td>
                            <td><?= html_escape($appointment['status']); ?></td>
                            <td class="center">
                                <a href="<?= site_url('/appointments/edit/'.$appointment['id']); ?>" class="edit-btn" title="Edit appointment">✏️</a>
                            </td>
                            <td class="center">
                                <form action="<?= site_url('/appointments/delete/'.$appointment['id']); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this appointment?');">
                                    <button type="submit" class="delete-btn" title="Remove appointment">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="empty-state">No appointments scheduled.</td>
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
