<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthSync Dashboard</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
          $activePage = 'dashboard';
          include('templates/sidebar.php'); 
        ?>
        <main class="dashboard">
            <div class="top-cards">
                <div class="card big-card">
                    <div class="sub-card">
                        <div class="icon red">👥</div>
                        <div class="number"><?= isset(
                            $totalPatients) ? html_escape($totalPatients) : '0' ?></div>
                        <div class="label">Active Patients</div>
                    </div>
                    <div class="divider"></div>
                    <div class="sub-card">
                        <div class="icon yellow">📅</div>
                        <div class="number"><?= isset($scheduledAppointments) ? html_escape($scheduledAppointments) : '0' ?></div>
                        <div class="label">Scheduled Appointments</div>
                    </div>
                </div>
            </div>

            <div class="bottom-cards">
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
            </div>

            <div class="chart-section">
                <div class="charts-row">
                    <div class="chart-card">
                        <h3>Incoming Patients</h3>
                        <div class="chart-buttons">
                            <button class="chart-btn active" data-range="weekly">Week</button>
                            <button class="chart-btn" data-range="monthly">Month</button>
                            <button class="chart-btn" data-range="yearly">Year</button>
                        </div>
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

        </main>
    </div>

    <?php include('templates/footer.php'); ?>

    <script>window.siteBasePath = '<?= rtrim(site_url('/'), '/') ?>';</script>
    <script src="<?= base_url(); ?>public/JS/dashboard-chart.js"></script>
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>
