<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Patient Form'); ?> - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = $context ?? 'patients';
        include('templates/sidebar.php'); 
        ?>

        <main class="dashboard">
            <?php
                $selectedType   = $patient['type'] ?? ($context === 'medications' ? 'Prescription' : 'Check-up');
                $selectedStatus = $patient['status'] ?? 'Pending';
            ?>
            <div class="form-card">
                <div class="form-card__header">
                    <h2><?= html_escape($title ?? 'Patient Form'); ?></h2>
                    <p>Complete the fields below to save the record.</p>
                </div>

                <form action="<?= $action; ?>" method="POST" class="crud-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="user_id">Linked User <span>*</span></label>
                            <select name="user_id" id="user_id">
                                <option value="">Select user</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id']; ?>" <?= isset($patient['user_id']) && (int)$patient['user_id'] === (int)$user['id'] ? 'selected' : ''; ?>>
                                        <?= html_escape($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="first_name">First Name <span>*</span></label>
                            <input type="text" id="first_name" name="first_name" value="<?= html_escape($patient['first_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name <span>*</span></label>
                            <input type="text" id="last_name" name="last_name" value="<?= html_escape($patient['last_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age <span>*</span></label>
                            <input type="number" id="age" name="age" min="0" value="<?= html_escape($patient['age'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= html_escape($patient['email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= html_escape($patient['address'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="disease">Condition / Notes</label>
                            <input type="text" id="disease" name="disease" value="<?= html_escape($patient['disease'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="type">Appointment Type</label>
                            <select name="type" id="type">
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type; ?>" <?= $selectedType === $type ? 'selected' : ''; ?>>
                                        <?= $type; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="schedule">Schedule</label>
                            <input type="date" id="schedule" name="schedule" value="<?= html_escape($patient['schedule'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="medicine">Medicine</label>
                            <input type="text" id="medicine" name="medicine" value="<?= html_escape($patient['medicine'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="duration">Duration / Dosage Instructions</label>
                            <input type="text" id="duration" name="duration" value="<?= html_escape($patient['duration'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status; ?>" <?= $selectedStatus === $status ? 'selected' : ''; ?>>
                                        <?= $status; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= site_url('/' . ($context ?? 'patients')); ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'Update' : 'Save'; ?> Record</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>

