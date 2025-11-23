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
                    <!-- Appointment Availability Notification -->
                    <div id="appointment-notification" class="notification" style="display: none; margin-bottom: 20px;">
                        <div class="notification-content">
                            <span id="notification-message"></span>
                        </div>
                    </div>

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
                            <label for="schedule">Schedule Date</label>
                            <input type="date" id="schedule" name="schedule" value="<?= html_escape(substr($patient['schedule'] ?? '', 0, 10)); ?>" min="<?= date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="schedule_time">Appointment Time</label>
                            <input type="time" id="schedule_time" name="schedule_time" value="<?= html_escape(strlen($patient['schedule'] ?? '') > 10 ? substr($patient['schedule'], 11, 5) : ''); ?>" min="08:00" max="17:00">
                        </div>

                        <div class="form-group">
                            <label for="medicine">Medicine</label>
                            <input type="text" id="medicine" name="medicine" value="<?= html_escape($patient['medicine'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="dose_per_day">Dose per Day (units)</label>
                            <input type="number" id="dose_per_day" name="dose_per_day" min="1" value="">
                            <small>How many units of this medicine are taken per day. Leave blank to use 1.</small>
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

    <script>
    // Only check appointment availability for appointment forms
    const isAppointmentForm = '<?= $context ?? '' ?>' === 'appointments';
    
    if (isAppointmentForm) {
        const scheduleInput = document.getElementById('schedule');
        const timeInput = document.getElementById('schedule_time');
        const notificationDiv = document.getElementById('appointment-notification');
        const notificationMessage = document.getElementById('notification-message');
        const submitBtn = document.querySelector('button[type="submit"]');
        const currentPatientId = '<?= ($patient['id'] ?? null) ?>';

        // Check availability when date or time changes
        function checkAvailability() {
            const date = scheduleInput?.value;
            const time = timeInput?.value;

            // Clear notification if either field is empty
            if (!date || !time) {
                notificationDiv.style.display = 'none';
                notificationDiv.className = 'notification';
                submitBtn.disabled = false;
                return;
            }

            // Build query string
            const params = new URLSearchParams({
                date: date,
                time: time
            });
            if (currentPatientId) {
                params.append('exclude_id', currentPatientId);
            }

            // Fetch availability from API
            fetch('<?= site_url('/api/check-appointment-availability') ?>?' + params)
                .then(response => response.json())
                .then(data => {
                    notificationDiv.style.display = 'flex';
                    notificationMessage.textContent = data.message;
                    
                    if (data.available) {
                        notificationDiv.className = 'notification notification-available';
                        submitBtn.disabled = false;
                    } else {
                        notificationDiv.className = 'notification notification-taken';
                        submitBtn.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error checking appointment availability:', error);
                    notificationDiv.style.display = 'none';
                });
        }

        // Add event listeners
        if (scheduleInput) {
            scheduleInput.addEventListener('change', checkAvailability);
        }
        if (timeInput) {
            timeInput.addEventListener('change', checkAvailability);
        }

        // Initial check if both fields have values
        if (scheduleInput?.value && timeInput?.value) {
            checkAvailability();
        }
    }
    </script>
</body>
</html>

