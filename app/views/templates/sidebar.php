<aside class="sidebar">
    <?php 
        $LAVA = function_exists('lava_instance') ? lava_instance() : null;
        $sessionRole = $LAVA ? ($LAVA->session->userdata('role') ?? 'user') : 'user';
    ?>
    <a href="<?= site_url('/dashboard') ?>" class="nav-btn <?= ($activePage=='dashboard') ? 'active' : '' ?>"><span>🏠</span> Dashboard</a>
    <a href="<?= site_url('/patients') ?>" class="nav-btn <?= ($activePage=='patients') ? 'active' : '' ?>"><span>👤</span> Patients</a>
    <a href="<?= site_url('/appointments') ?>" class="nav-btn <?= ($activePage=='appointments') ? 'active' : '' ?>"><span>📅</span> Appointments</a>
    <a href="<?= site_url('/medications') ?>" class="nav-btn <?= ($activePage=='medications') ? 'active' : '' ?>"><span>💊</span> Medications</a>

    <?php if ($sessionRole === 'admin'): ?>
        <a href="<?= site_url('/inventory') ?>" class="nav-btn <?= ($activePage=='inventory') ? 'active' : '' ?>"><span>📦</span> Inventory</a>
        <a href="<?= site_url('/admin/users') ?>" class="nav-btn <?= ($activePage=='admin-users') ? 'active' : '' ?>"><span>👥</span> Manage Users</a>
    <?php endif; ?>
    <a href="<?= site_url('/records') ?>" class="nav-btn <?= ($activePage=='records') ? 'active' : '' ?>"><span>📋</span> Records</a>
    <a href="<?= site_url('/profile') ?>" class="nav-btn <?= ($activePage=='profile') ? 'active' : '' ?>"><span>👨</span> My Profile</a>
</aside>
