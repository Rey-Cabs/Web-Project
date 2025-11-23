<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medications - HealthSync</title>
    <link rel="stylesheet" href="<?= base_url(); ?>public/CSS/Style.css">
    <style>
        .patients-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
        }
        .patients-header h2 { 
            margin: 0; 
            font-size: 1.8rem; 
            color: var(--brand); 
        }
        .table-container { overflow-x: auto; }
        .patients-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 0.95rem; 
            min-width: 800px; 
        }
        .patients-table th, .patients-table td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: left; 
        }
        .patients-table th { 
            background-color: var(--brand); 
            color: #fff; 
            font-weight: 600; 
            border-bottom: 2px solid #932822;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .patients-table tr:nth-child(even) { background-color: #f9f9f9; }
        .patients-table tr:hover { background-color: #f0dbd5; }
        .center { text-align: center; }
        .empty-state { text-align: center; padding: 20px; font-style: italic; color: var(--brand); }
        .btn-pdf { 
            background-color: var(--brand); 
            color: white; 
            padding: 8px 15px; 
            border-radius: 5px; 
            text-decoration: none; 
            margin-left: 10px; 
        }
        .btn-pdf:hover { background-color: #932822; }
        .edit-btn, .delete-btn {
            background-color: var(--brand);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .edit-btn:hover, .delete-btn:hover { background-color: #932822; }
        .search-form { display: flex; gap: 10px; align-items: center; }
        .search { padding: 5px 10px; border-radius: 4px; border: 1px solid var(--brand); }
        .btn { padding: 6px 12px; border-radius: 4px; border: none; background-color: var(--brand); color: white; cursor: pointer; }
        .btn:hover { background-color: #932822; }
        .inline-form { display: inline-block; margin: 0; }
    </style>
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
                <p class="table-subtitle">Track your past, ongoing, and upcoming prescriptions along with their duration.</p>
            </div>
            <div class="header-actions">
                <form action="<?= site_url('/medications'); ?>" method="get" class="search-form">
                    <input class="search" name="q" type="text" placeholder="Search medications" value="<?= html_escape($search_term ?? ''); ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <?php if($role === 'admin'): ?>
                    <a href="<?= site_url('/medications/export_pdf'); ?>" class="btn-pdf">Download PDF</a>
                <?php endif; ?>
                <a href="<?= site_url('/medications/create'); ?>" class="btn btn-primary">+ Add Medication</a>
            </div>
        </div>

        <div class="table-container">
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <?php if($role === 'admin'): ?><th>User</th><?php endif; ?>
                        <th>Medicine</th>
                        <th>Disease</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="center">Edit</th>
                        <th class="center">Remove</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($medications)): ?>
                    <?php 
                    $today = new DateTime();
                    foreach($medications as $medication): 

                        // Skip if user is not admin and user_id does not match
                        if ($role !== 'admin' && ($userId ?? null) !== ($medication['user_id'] ?? null)) continue;

                        $medicine = !empty($medication['medicine']) ? $medication['medicine'] : 'None';
                        $disease  = !empty($medication['disease']) ? $medication['disease'] : 'None';
                        $start    = !empty($medication['start_date']) ? new DateTime($medication['start_date']) : null;
                        $end      = !empty($medication['end_date']) ? new DateTime($medication['end_date']) : null;

                        $duration = ($start && $end) ? $start->diff($end)->format('%a days') : 'None';

                        if (!$start || !$end) {
                            $status = 'Unknown';
                        } elseif ($today < $start) {
                            $status = 'Upcoming';
                        } elseif ($today > $end) {
                            $status = 'Completed';
                        } else {
                            $status = 'Ongoing';
                        }

                        $id = $medication['id'] ?? 'None';
                    ?>
                    <tr>
                        <td><?= html_escape($id); ?></td>
                        <?php if($role === 'admin'): ?>
                            <td><?= html_escape(($medication['first_name'] ?? 'Unknown') . ' ' . ($medication['last_name'] ?? '')); ?></td>
                        <?php endif; ?>
                        <td><?= html_escape($medicine); ?></td>
                        <td><?= html_escape($disease); ?></td>
                        <td><?= $start ? html_escape($start->format('Y-m-d')) : 'None'; ?></td>
                        <td><?= $end ? html_escape($end->format('Y-m-d')) : 'None'; ?></td>
                        <td><?= html_escape($duration); ?></td>
                        <td><?= html_escape($status); ?></td>
                        <td class="center">
                            <?php if($id !== 'None'): ?>
                                <a href="<?= site_url('/medications/edit/'.$id); ?>" class="edit-btn" title="Edit medication">✏️</a>
                            <?php else: ?> None <?php endif; ?>
                        </td>
                        <td class="center">
                            <?php if($id !== 'None'): ?>
                                <form action="<?= site_url('/medications/delete/'.$id); ?>" method="POST" class="inline-form" onsubmit="return confirm('Remove this medication plan?');">
                                    <button type="submit" class="delete-btn" title="Remove medication">🗑️</button>
                                </form>
                            <?php else: ?> None <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $role==='admin'?10:8 ?>" class="empty-state">No medication plans recorded.</td>
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
