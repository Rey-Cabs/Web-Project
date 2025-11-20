<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'inventory';
        include('templates/sidebar.php'); 
        ?>

        <main class="dashboard">
            <div class="patients-header">
                <div>
                    <h2>Inventory</h2>
                </div>
                <div class="header-actions">
                    <form action="<?= site_url('/inventory'); ?>" method="get" class="search-form">
                        <input class="search" name="q" type="text" placeholder="Search inventory" value="<?= html_escape($search_term ?? ''); ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                    <a href="<?= site_url('/inventory/create'); ?>" class="btn btn-primary">+ Add</a>
                </div>
            </div>

            <div class="top-cards">
                <div class="card big-card">
                    <div class="sub-card">
                        <div class="icon red">💊</div>
                        <div class="number"><?= (int) ($total_main ?? 0); ?></div>
                        <div class="label">Main Inventory</div>
                    </div>
                    <div class="divider"></div>
                    <div class="sub-card">
                        <div class="icon yellow">📦</div>
                        <div class="number"><?= (int) ($total_reserve ?? 0); ?></div>
                        <div class="label">Reserve Inventory</div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Batch Code</th>
                            <th>Received</th>
                            <th>Expiry</th>
                            <th>Total Qty</th>
                            <th>Remaining</th>
                            <th>Location</th>
                            <th class="center">Edit</th>
                            <th class="center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if(!empty($batches)): ?>
                        <?php foreach($batches as $batch): ?>
                        <?php
                            $received = !empty($batch['received_date']) ? substr($batch['received_date'], 0, 10) : '--';
                            $expiry   = !empty($batch['expiry_date']) ? substr($batch['expiry_date'], 0, 10) : '--';
                        ?>
                        <tr>
                            <td><?= html_escape($batch['item_name']); ?></td>
                            <td><?= html_escape($batch['batch_code']); ?></td>
                            <td><?= html_escape($received); ?></td>
                            <td><?= html_escape($expiry); ?></td>
                            <td><?= html_escape($batch['quantity']); ?></td>
                            <td><?= html_escape($batch['remaining_quantity']); ?></td>
                            <td><?= html_escape(ucfirst($batch['location'])); ?></td>
                            <td class="center">
                                <a href="<?= site_url('/inventory/edit/'.$batch['batch_id']); ?>" class="edit-btn" title="Edit batch">✏️</a>
                            </td>
                            <td class="center">
                                <form action="<?= site_url('/inventory/delete/'.$batch['batch_id']); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this batch?');">
                                    <button type="submit" class="delete-btn" title="Remove batch">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="empty-state">No inventory batches recorded.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($pagination)) echo $pagination; ?>
            </div>

            <div class="chart-section">
                <div class="chart-card">
                    <h3>Stock by Item</h3>
                    <canvas id="inventoryChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>

    <script>
        (function() {
            const ctx = document.getElementById('inventoryChart');
            if (!ctx) return;

            const labels = [
                <?php if (!empty($summary)) : ?>
                    <?php foreach ($summary as $row): ?>
                        '<?= addslashes($row['item_name']); ?>',
                    <?php endforeach; ?>
                <?php endif; ?>
            ];

            const mainData = [
                <?php if (!empty($summary)) : ?>
                    <?php foreach ($summary as $row): ?>
                        <?= (int) ($row['main_qty'] ?? 0); ?>,
                    <?php endforeach; ?>
                <?php endif; ?>
            ];

            const reserveData = [
                <?php if (!empty($summary)) : ?>
                    <?php foreach ($summary as $row): ?>
                        <?= (int) ($row['reserve_qty'] ?? 0); ?>,
                    <?php endforeach; ?>
                <?php endif; ?>
            ];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Main',
                            data: mainData,
                            backgroundColor: '#b73b2f'
                        },
                        {
                            label: 'Reserve',
                            data: reserveData,
                            backgroundColor: '#fbbc04'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        })();
    </script>
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>

