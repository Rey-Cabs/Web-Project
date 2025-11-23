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

            <?php
                $LAVA = lava_instance();
                $logged_in = $LAVA->session->userdata('logged_in') ?? false;
                $first = $LAVA->session->userdata('first_name') ?? '';
            ?>

            <?php if ($logged_in): ?>

                <!-- Hello + Name -->
                <button id="userMenuBtn" class="btn btn-secondary"
                        style="margin-left: 15px; color: #fff !important; font-weight:700; border-radius:10px; border-color:#000;">
                    Hello, <?= html_escape($first); ?>
                </button>

                <!-- Dropdown Modal -->
                <div id="userMenuModal" class="user-menu-modal"
                     role="dialog" aria-hidden="true"
                     style="display:none; position:absolute; right:10px; top:48px; 
                            z-index:999; background:#fff; border:1px solid #ddd;
                            box-shadow:0 2px 8px rgba(0,0,0,0.1); padding:8px; width:130px;">

                    <!-- Profile (bordered like logout) -->
                    <a href="<?= site_url('/profile'); ?>" 
                       class="btn btn-link"
                       style="display:block; width:100%; text-align:left;
                              padding:6px 8px; margin-bottom:5px;
                              border:1px solid #000; border-radius:4px; 
                              color:#000 !important; font-weight:500;">
                       Profile
                    </a>

                    <!-- Logout -->
                    <button id="logoutBtn" 
                            class="btn btn-link"
                            style="display:block; width:100%; text-align:left;
                                   padding:6px 8px; border:1px solid #000; 
                                   border-radius:4px; color:#000 !important; 
                                   font-weight:500;">
                        Log out
                    </button>
                </div>

            <?php else: ?>
                <!-- Show Login if not logged in -->
                <a href="<?= site_url('/auth/login'); ?>" class="btn btn-primary" style="margin-left: 15px;">
                    Login
                </a>
            <?php endif; ?>

        </nav>
    </div>
</header>
