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
<?php 
include('templates/header.php'); 
$LAVA = lava_instance();
?>

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
                <?php if ($role === 'admin' || $isPatient): ?>
                <form action="<?= site_url('/appointments'); ?>" method="get" class="search-form">
                    <input class="search" name="q" type="text" placeholder="Search appointments" value="<?= html_escape($search_term ?? ''); ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <a href="<?= site_url('/appointments/create'); ?>" class="btn btn-primary">+ Schedule Appointment</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <?php if ($role === 'admin'): ?>
                <!-- Admin sees all appointments -->
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

            <?php elseif ($isPatient): ?>
                <!-- Regular user sees only their appointments -->
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Disease</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($appointments)): ?>
                        <?php foreach($appointments as $appointment): ?>
                        <tr>
                            <td><?= html_escape($appointment['disease']); ?></td>
                            <td><?= html_escape($appointment['type']); ?></td>
                            <td><?= html_escape($appointment['schedule']); ?></td>
                            <td><?= html_escape($appointment['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">No appointments scheduled.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($pagination)) echo $pagination; ?>

            <?php elseif ($show_register_prompt): ?>
                <!-- User is not registered as patient -->
                <div class="register-prompt" style="text-align:center; margin-top:40px;">
                    <p>You are not yet registered as a patient. To schedule appointments, please</p>
                    <?php if (!$LAVA->session->userdata('logged_in')): ?>
                        <a href="<?= site_url('/auth/login'); ?>" class="btn btn-primary">
                            Admit as Patient
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('/patients/create'); ?>" class="btn btn-primary">
                            Admit as Patient
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include('templates/footer.php'); ?>
<script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>
