<!-- Sidebar container -->
<aside class="sidebar">
    <a href="<?= site_url('/dashboard') ?>" class="nav-btn <?= ($activePage=='dashboard') ? 'active' : '' ?>"><span>🏠</span> Dashboard</a>
    <a href="<?= site_url('/patients') ?>" class="nav-btn <?= ($activePage=='patients') ? 'active' : '' ?>"><span>👤</span> Patients</a>
    <a href="<?= site_url('/appointments') ?>" class="nav-btn <?= ($activePage=='appointments') ? 'active' : '' ?>"><span>📅</span> Appointments</a>
    <a href="<?= site_url('/medications') ?>" class="nav-btn <?= ($activePage=='medications') ? 'active' : '' ?>"><span>💊</span> Medications</a>
    <a href="<?= site_url('/inventory') ?>" class="nav-btn <?= ($activePage=='inventory') ? 'active' : '' ?>"><span>📦</span> Inventory</a>
    <a href="<?= site_url('/records') ?>" class="nav-btn <?= ($activePage=='records') ? 'active' : '' ?>"><span>📋</span> Records</a>
</aside>
 </aside>
