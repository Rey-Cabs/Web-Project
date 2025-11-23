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
                        <?php $selectedCategory = $category ?? ''; ?>
                        <select name="category" class="search">
                            <option value="" <?= $selectedCategory === '' ? 'selected' : ''; ?>>All types</option>
                            <option value="Medicine" <?= $selectedCategory === 'Medicine' ? 'selected' : ''; ?>>Medicine</option>
                            <option value="Equipment" <?= $selectedCategory === 'Equipment' ? 'selected' : ''; ?>>Equipment</option>
                            <option value="Supply" <?= $selectedCategory === 'Supply' ? 'selected' : ''; ?>>Supply</option>
                            <option value="Other" <?= $selectedCategory === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
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

            <?php
            $lowMain = [];
            if (!empty($summary)) {
                foreach ($summary as $row) {
                    $mainQty    = (int) ($row['main_qty'] ?? 0);
                    $reserveQty = (int) ($row['reserve_qty'] ?? 0);
                    $critical   = (int) ($row['critical_level'] ?? 10);
                    if ($critical <= 0) {
                        $critical = 10;
                    }
                    if ($mainQty <= $critical) {
                        $lowMain[] = [
                            'name'    => $row['item_name'] ?? '',
                            'main'    => $mainQty,
                            'reserve' => $reserveQty,
                        ];
                    }
                }
            }
            ?>

            <?php if (!empty($lowMain)) : ?>
            <div class="alert-card warning">
                <h3>Items with low main stock (≤ 10)</h3>
                <ul>
                    <?php foreach ($lowMain as $item): ?>
                        <li>
                            <?= html_escape($item['name']); ?> - Main: <?= (int) $item['main']; ?>, Reserve: <?= (int) $item['reserve']; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Stocks</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($summary)): ?>
                        <?php foreach ($summary as $row): ?>
                        <?php
                            $mainQty    = (int) ($row['main_qty'] ?? 0);
                            $reserveQty = (int) ($row['reserve_qty'] ?? 0);
                            $totalQty   = $mainQty + $reserveQty;
                            $critical   = (int) ($row['critical_level'] ?? 10);
                            if ($critical <= 0) {
                                $critical = 10;
                            }

                            $statusLabel = 'OK';
                            if ($mainQty <= $critical && $reserveQty > 0) {
                                $statusLabel = 'Main low - refill from reserve';
                            } elseif ($mainQty <= $critical && $reserveQty <= 0) {
                                $statusLabel = 'Need refill - no reserve';
                            } elseif ($totalQty <= $critical) {
                                $statusLabel = 'Critical';
                            } elseif ($totalQty <= ($critical * 1.5)) {
                                $statusLabel = 'Low';
                            }
                        ?>
                        <tr>
                            <td><?= html_escape($row['item_name']); ?></td>
                            <td><?= $totalQty; ?></td>
                            <td><?= html_escape($statusLabel); ?></td>
                            <td class="actions-cell">
                                <?php $rowBatchId = (int)($row['batch_id'] ?? 0); ?>
                                <?php if ($rowBatchId > 0): ?>
                                    <a href="<?= site_url('/inventory/edit/'.$rowBatchId); ?>" class="btn btn-small">Edit</a>
                                    <form action="<?= site_url('/inventory/delete/'.$rowBatchId); ?>" method="post" style="display:inline-block;">
                                        <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this batch?');">Delete</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($mainQty <= $critical && $reserveQty > 0): ?>
                                    <form action="<?= site_url('/inventory/refill/'.(int)($row['item_id'] ?? 0)); ?>" method="post" style="display:inline-block;">
                                        <button type="submit" class="btn btn-small">Refill from reserve</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($rowBatchId <= 0 && !($mainQty <= $critical && $reserveQty > 0)): ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">No inventory items recorded.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
                <?php if (!empty($pagination)) echo $pagination; ?>
            </div>

            <div class="table-container">
                <h3>Reserve Batches</h3>
                <table class="patients-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stocks</th>
                            <th>Manufacture Date</th>
                            <th>Expiration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $hasReserveBatches = false;
                    if (!empty($batches)):
                        foreach ($batches as $batch):
                            if (($batch['location'] ?? '') !== 'reserve') {
                                continue;
                            }
                            $hasReserveBatches = true;
                    ?>
                        <tr>
                            <td><?= html_escape($batch['item_name'] ?? ''); ?></td>
                            <td><?= (int)($batch['remaining_quantity'] ?? 0); ?></td>
                            <td><?= html_escape(isset($batch['manufacture_date']) ? substr($batch['manufacture_date'], 0, 10) : ''); ?></td>
                            <td><?= html_escape(isset($batch['expiry_date']) ? substr($batch['expiry_date'], 0, 10) : ''); ?></td>
                            <td class="actions-cell">
                                <a href="<?= site_url('/inventory/edit/'.(int)($batch['batch_id'] ?? 0)); ?>" class="btn btn-small">Edit</a>
                                <form action="<?= site_url('/inventory/delete/'.(int)($batch['batch_id'] ?? 0)); ?>" method="post" style="display:inline-block;">
                                    <button type="submit" class="btn btn-small btn-danger" onclick="return confirm('Are you sure you want to delete this batch?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                        endforeach;
                    endif;
                    ?>
                    <?php if (!$hasReserveBatches): ?>
                        <tr>
                            <td colspan="5" class="empty-state">No reserve batches recorded.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
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

