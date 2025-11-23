<?php
// dashboard.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSync Dashboard</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* User dashboard enhancements */
        .welcome-section h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .welcome-section p {
            font-size: 1.1rem;
            color: #34495e;
        }
        .card .label {
            font-weight: 600;
            font-size: 0.95rem;
            color: #555;
        }
        .card .number {
            font-size: 1.6rem;
            font-weight: 700;
            color: #222;
        }
        .table-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .empty-state {
            color: #7f8c8d;
            font-style: italic;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
<?php include('templates/header.php'); ?>

<div class="main-container">
    <?php 
      $activePage = 'dashboard';
      include('templates/sidebar.php'); 
    ?>
    <main class="dashboard">

        <!-- Welcome Section -->
        <section class="welcome-section">
            <h2>Hello, <?= html_escape($user['first_name'] ?? 'User'); ?>!</h2>
            <p>Here's a quick overview of your health activity.</p>
            <?php if($user['role'] === 'admin'): ?>
            <div style="margin-top: 20px;">
                <a href="<?= site_url('/admin/users'); ?>" style="display: inline-block; padding: 10px 20px; background: #b73b2f; color: white; border-radius: 4px; text-decoration: none; margin-right: 10px;">Manage Users</a>
                <a href="<?= site_url('/profile'); ?>" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; border-radius: 4px; text-decoration: none;">My Profile</a>
            </div>
            <?php else: ?>
            <div style="margin-top: 20px;">
                <a href="<?= site_url('/profile'); ?>" style="display: inline-block; padding: 10px 20px; background: #b73b2f; color: white; border-radius: 4px; text-decoration: none;">View My Profile</a>
            </div>
            <?php endif; ?>
        </section>

        <!-- Top Cards -->
        <div class="top-cards">
            <?php if($user['role'] === 'admin'): ?>
            <div class="card big-card">
                <div class="sub-card">
                    <div class="icon red">👥</div>
                    <div class="number"><?= isset($totalPatients) ? html_escape($totalPatients) : '0' ?></div>
                    <div class="label">Active Patients</div>
                </div>
                <div class="divider"></div>
                <div class="sub-card">
                    <div class="icon yellow">📅</div>
                    <div class="number"><?= isset($scheduledAppointments) ? html_escape($scheduledAppointments) : '0' ?></div>
                    <div class="label">Scheduled Appointments</div>
                </div>
            </div>
            <?php else: ?>
            <div class="card big-card">
                <div class="sub-card">
                    <div class="icon yellow">📅</div>
                    <div class="number"><?= isset($upcomingAppointments) ? count($upcomingAppointments) : '0' ?></div>
                    <div class="label">Your Upcoming Appointments</div>
                </div>
                <div class="divider"></div>
                <div class="sub-card">
                    <div class="icon green">💊</div>
                    <div class="number"><?= isset($activeMedications) ? count($activeMedications) : '0' ?></div>
                    <div class="label">Your Active Medications</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bottom Cards -->
        <div class="bottom-cards">
            <?php if($user['role'] === 'admin'): ?>
            <div class="card">
                <div class="icon red">🩺</div>
                <div class="number"><?= isset($newPatients) ? html_escape($newPatients) : '0' ?></div>
                <div class="label">New Patients (30d)</div>
            </div>
            <div class="card">
                <div class="icon yellow">💊</div>
                <div class="number"><?= isset($pendingPrescriptions) ? html_escape($pendingPrescriptions) : '0' ?></div>
                <div class="label">Pending Prescriptions</div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="icon red">🩺</div>
                <div class="number"><?= isset($pastAppointments) ? count($pastAppointments) : '0' ?></div>
                <div class="label">Past Appointments</div>
            </div>
            <div class="card">
                <div class="icon yellow">💊</div>
                <div class="number"><?= isset($completedMedications) ? count($completedMedications) : '0' ?></div>
                <div class="label">Completed Medications</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quick Tables for Users -->
        <?php if($user['role'] !== 'admin'): ?>
        <section class="user-tables">
            <!-- Upcoming Appointments -->
            <div class="table-section">
                <h3>Your Upcoming Appointments</h3>
                <?php if(!empty($upcomingAppointments)): ?>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Doctor / Department</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($upcomingAppointments as $app): ?>
                        <tr>
                            <td><?= isset($app['schedule']) ? html_escape(date('M d, Y h:i A', strtotime($app['schedule']))) : '-' ?></td>
                            <td><?= isset($app['doctor']) ? html_escape($app['doctor']) : '-' ?></td>
                            <td><?= isset($app['status']) ? html_escape($app['status']) : '-' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="<?= site_url('/appointments'); ?>" class="btn btn-secondary mt-2">View All</a>
                <?php else: ?>
                <p class="empty-state">No upcoming appointments.</p>
                <?php endif; ?>
            </div>

            <!-- Active Medications -->
            <div class="table-section">
                <h3>Your Active Medications</h3>
                <?php if(!empty($activeMedications)): ?>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Disease</th>
                            <th>Schedule</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($activeMedications as $med): ?>
                        <tr>
                            <td><?= html_escape($med['medicine'] ?? '-') ?></td>
                            <td><?= html_escape($med['disease'] ?? '-') ?></td>
                            <td><?= html_escape($med['schedule'] ?? '-') ?></td>
                            <td><?= html_escape($med['duration'] ?? '-') ?></td>
                            <td><?= html_escape($med['status'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="<?= site_url('/medications'); ?>" class="btn btn-secondary mt-2">View All</a>
                <?php else: ?>
                <p class="empty-state">No active medications.</p>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Charts Section (Admins Only) -->
        <?php if($user['role'] === 'admin'): ?>
        <div class="chart-section">
            <div class="charts-row">
                <div class="chart-card">
                    <h3>Incoming Patients</h3>
                    <canvas id="barChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Patients Diseases</h3>
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="chart-section">
            <div class="chart-card">
                <h3>Predictive Analysis of New Patients</h3>
                <canvas id="lineChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<?php include('templates/footer.php'); ?>

<script>window.siteBasePath = '<?= rtrim(site_url('/'), '/') ?>';</script>
<script src="<?= base_url(); ?>public/JS/dashboard-chart.js"></script>
<script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>
