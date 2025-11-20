<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Inventory Form'); ?> - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
</head>
<body>
    <?php include('templates/header.php'); ?>

    <div class="main-container">
        <?php 
        $activePage = 'inventory';
        include('templates/sidebar.php'); 
        ?>

        <main class="dashboard">
            <div class="form-card">
                <div class="form-card__header">
                    <h2><?= html_escape($title ?? 'Inventory Form'); ?></h2>
                    <p>Specify batch information to keep inventory up-to-date.</p>
                </div>

                <form action="<?= $action; ?>" method="POST" class="crud-form">
                    <div class="form-grid two-column">
                        <div class="form-group">
                            <label for="item_id">Existing Item</label>
                            <select name="item_id" id="item_id">
                                <option value="">-- Select Item --</option>
                                <?php foreach ($items as $item): ?>
                                    <option value="<?= $item['item_id']; ?>" <?= isset($batch['item_id']) && (int)$batch['item_id'] === (int)$item['item_id'] ? 'selected' : ''; ?>>
                                        <?= html_escape($item['item_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="new_item_name">New Item Name</label>
                            <input type="text" id="new_item_name" name="new_item_name" value="">
                            <small>Fill this to create a new item instead of selecting one.</small>
                        </div>

                        <div class="form-group">
                            <label for="new_item_description">New Item Description</label>
                            <textarea id="new_item_description" name="new_item_description" rows="2"><?= html_escape($batch['item_description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category">
                                <?php 
                                $category = $batch['category'] ?? 'Medicine';
                                $categories = ['Medicine','Equipment','Supply','Other'];
                                foreach ($categories as $cat): ?>
                                    <option value="<?= $cat; ?>" <?= $category === $cat ? 'selected' : ''; ?>><?= $cat; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unit">Unit</label>
                            <input type="text" id="unit" name="unit" value="<?= html_escape($batch['unit'] ?? 'pcs'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="critical_level">Critical Level</label>
                            <input type="number" min="0" id="critical_level" name="critical_level" value="<?= html_escape($batch['critical_level'] ?? 10); ?>">
                        </div>

                        <div class="form-group">
                            <label for="batch_code">Batch Code <span>*</span></label>
                            <input type="text" id="batch_code" name="batch_code" value="<?= html_escape($batch['batch_code'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity <span>*</span></label>
                            <input type="number" id="quantity" name="quantity" min="1" value="<?= html_escape($batch['quantity'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="remaining_quantity">Remaining Quantity</label>
                            <input type="number" id="remaining_quantity" name="remaining_quantity" min="0" value="<?= html_escape($batch['remaining_quantity'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <select name="location" id="location">
                                <?php $location = $batch['location'] ?? 'main'; ?>
                                <option value="main" <?= $location === 'main' ? 'selected' : ''; ?>>Main</option>
                                <option value="reserve" <?= $location === 'reserve' ? 'selected' : ''; ?>>Reserve</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="received_date">Received Date</label>
                            <input type="date" id="received_date" name="received_date" value="<?= html_escape($batch['received_date'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="manufacture_date">Manufacture Date</label>
                            <input type="date" id="manufacture_date" name="manufacture_date" value="<?= html_escape($batch['manufacture_date'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="expiry_date">Expiry Date</label>
                            <input type="date" id="expiry_date" name="expiry_date" value="<?= html_escape($batch['expiry_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= site_url('/inventory'); ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><?= ($mode ?? 'create') === 'edit' ? 'Update Batch' : 'Save Batch'; ?></button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?php include('templates/footer.php'); ?>
    <script src="<?= base_url(); ?>public/JS/script.js"></script>
</body>
</html>

