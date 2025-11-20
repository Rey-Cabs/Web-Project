<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'patients';
        include('templates/sidebar.php'); 
        ?>
        <main class="dashboard">
            <div class="patients-header">
                <div>
                    <h2>Patients</h2>
                    <p class="table-subtitle">Manage patient records and basic demographics.</p>
                </div>
                <div class="header-actions">
                    <form action="<?= site_url('/patients'); ?>" method="get" class="search-form">
                        <input class="search" name="q" type="text" placeholder="Search patients" value="<?= html_escape($search_term ?? ''); ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                    <a href="<?= site_url('/patients/create'); ?>" class="btn btn-primary">+ Add Patient</a>
                </div>
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
                    <?php if(!empty($patients)): ?>
                        <?php foreach($patients as $patient): ?>
                        <tr>
                            <td><?= html_escape($patient['id']); ?></td>
                            <td><?= html_escape($patient['first_name']); ?></td>
                            <td><?= html_escape($patient['last_name']); ?></td>
                            <td><?= html_escape($patient['age']); ?></td>
                            <td><?= html_escape($patient['email']); ?></td>
                            <td><?= html_escape($patient['address']); ?></td>
                            <td><?= html_escape($patient['disease']); ?></td>
                            <td><?= html_escape($patient['type']); ?></td>
                            <td><?= html_escape($patient['status']); ?></td>
                            <td class="center">
                                <a href="<?= site_url('/patients/edit/'.$patient['id']); ?>" class="edit-btn" title="Edit patient">✏️</a>
                            </td>
                            <td class="center">
                                <form action="<?= site_url('/patients/delete/'.$patient['id']); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this patient record?');">
                                    <button type="submit" class="delete-btn" title="Remove patient">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="empty-state">No patient records found.</td>
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
