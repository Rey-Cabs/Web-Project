<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'medications';
        include('templates/sidebar.php'); 
        ?>

        <main class="dashboard">
            <div class="patients-header">
                <div>
                    <h2>Medication Plans</h2>
                    <p class="table-subtitle">Track ongoing prescriptions and treatment timelines.</p>
                </div>
                <div class="header-actions">
                    <form action="<?= site_url('/medications'); ?>" method="get" class="search-form">
                        <input class="search" name="q" type="text" placeholder="Search medications" value="<?= html_escape($search_term ?? ''); ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                    <a href="<?= site_url('/medications/create'); ?>" class="btn btn-primary">+ Add Medication</a>
                </div>
            </div>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Disease</th>
                            <th>Medicine</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="center">Edit</th>
                            <th class="center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($medications)): ?>
                        <?php foreach($medications as $medication): ?>
                        <tr>
                            <td><?= html_escape($medication['id']); ?></td>
                            <td><?= html_escape($medication['first_name'] . ' ' . $medication['last_name']); ?></td>
                            <td><?= html_escape($medication['disease']); ?></td>
                            <td><?= html_escape($medication['medicine']); ?></td>
                            <td><?= html_escape($medication['duration']); ?></td>
                            <td><?= html_escape($medication['status']); ?></td>
                            <td class="center">
                                <a href="<?= site_url('/medications/edit/'.$medication['id']); ?>" class="edit-btn" title="Edit medication">✏️</a>
                            </td>
                            <td class="center">
                                <form action="<?= site_url('/medications/delete/'.$medication['id']); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this medication plan?');">
                                    <button type="submit" class="delete-btn" title="Remove medication">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">No medication plans recorded.</td>
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
