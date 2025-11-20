<header class="topbar">
    <div class="logo"> 
        <span class="logo-icon">+</span> 
        <span class="logo-text">HealthSync</span>
    </div>

    <div class="top-controls">
        <nav class="topnav" role="navigation" aria-label="Main Navigation">
            <a href="<?= site_url('/'); ?>">Home</a>
            <a href="<?= site_url('/dashboard'); ?>">Dashboard</a>
            <a href="<?= site_url('/about'); ?>">About</a>
            <a href="<?= site_url('/contact'); ?>">Contact</a>
                <?php if (function_exists('logged_in') && logged_in()): ?>
                    <?php
                        $LAVA = lava_instance();
                        $full = $LAVA->session->userdata('user_name') ?? '';
                        $first = $full !== '' ? explode(' ', trim($full))[0] : 'Account';
                    ?>
                    <button id="userMenuBtn" class="btn btn-secondary">Hello, <?= html_escape($first); ?></button>
                    <div id="userMenuModal" class="user-menu-modal" role="dialog" aria-hidden="true" style="display:none; position:absolute; right:10px; top:48px; z-index:999; background:#fff; border:1px solid #ddd; box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:8px;">
                        <button id="logoutBtn" class="btn btn-link">Log out</button>
                    </div>
                <?php else: ?>
                    <a href="<?= site_url('/auth/login'); ?>">Login</a>
                <?php endif; ?>
        </nav>
        <!-- user menu modal (rendered inline next to the user button) -->
    </div>
</header>
