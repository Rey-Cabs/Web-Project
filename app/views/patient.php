<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>

<?php 
include('templates/header.php'); 
$LAVA = lava_instance();
?>

<div class="main-container">
    <?php 
    $activePage = 'patients';
    include('templates/sidebar.php');
    ?>

    <main class="dashboard">

        <div class="patients-header">
            <h2>Patients</h2>
        </div>

        <!-- ------------------------------------------- -->
        <!--   CASE 1: ADMIN → Show full table            -->
        <!-- ------------------------------------------- -->

        <?php if ($role === 'admin'): ?>

            <div class="header-actions">
                <form action="<?= site_url('/patients'); ?>" method="get" class="search-form">
                    <input class="search" name="q" type="text" placeholder="Search patients"
                           value="<?= html_escape($search_term ?? ''); ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <a href="<?= site_url('/patients/create'); ?>" class="btn btn-primary">+ Add Patient</a>
            </div>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Age</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th>Disease</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th class="center">Edit</th>
                            <th class="center">Remove</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($patients)): ?>
                            <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td><?= $patient['id']; ?></td>
                                    <td><?= $patient['first_name']; ?></td>
                                    <td><?= $patient['last_name']; ?></td>
                                    <td><?= $patient['age']; ?></td>
                                    <td><?= $patient['email']; ?></td>
                                    <td><?= $patient['address']; ?></td>
                                    <td><?= $patient['disease']; ?></td>
                                    <td><?= $patient['type']; ?></td>
                                    <td><?= $patient['status']; ?></td>
                                    <td class="center">
                                        <a href="<?= site_url('/patients/edit/'.$patient['id']); ?>" class="edit-btn">✏️</a>
                                    </td>
                                    <td class="center">
                                        <form action="<?= site_url('/patients/delete/'.$patient['id']); ?>" method="POST"
                                              onsubmit="return confirm('Remove this patient record?');">
                                            <button type="submit" class="delete-btn">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="11" class="empty-state">No patient records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($pagination)) echo $pagination; ?>
            </div>

        <!-- ------------------------------------------- -->
        <!--   CASE 2: LOGGED IN USER → Show own info     -->
        <!-- ------------------------------------------- -->

        <?php elseif ($isPatient): ?>

            <h3>Your Patient Information</h3>

            <div class="patient-info-card">

                <div class="info-row"><strong>First Name:</strong> <?= $patientInfo['first_name']; ?></div>
                <div class="info-row"><strong>Last Name:</strong> <?= $patientInfo['last_name']; ?></div>
                <div class="info-row"><strong>Age:</strong> <?= $patientInfo['age']; ?></div>
                <div class="info-row"><strong>Email:</strong> <?= $patientInfo['email']; ?></div>
                <div class="info-row"><strong>Address:</strong> <?= $patientInfo['address']; ?></div>
                <div class="info-row"><strong>Disease:</strong> <?= $patientInfo['disease']; ?></div>
                <div class="info-row"><strong>Type:</strong> <?= $patientInfo['type']; ?></div>
                <div class="info-row"><strong>Status:</strong> <?= $patientInfo['status']; ?></div>

            </div>

        <!-- ------------------------------------------- -->
        <!--   CASE 3: USER NOT A PATIENT                 -->
        <!-- ------------------------------------------- -->

        <?php else: ?>

            <div class="empty-state">
                <h3>You are not registered as a patient yet.</h3>
                <p>Would you like to be admitted as a patient?</p><br>

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

    </main>
</div>

<?php include('templates/footer.php'); ?>
<script src="<?= base_url(); ?>public/JS/script.js"></script>

</body>
</html>
