// Placeholder for future interactive dashboard features
console.log("Dashboard loaded");
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');

    // Sidebar is fixed and non-collapsible per design change. Ensure any persisted
    // collapsed state from previous versions does not hide it.
    try {
        if (document.body.classList.contains('sidebar-collapsed')) {
            document.body.classList.remove('sidebar-collapsed');
        }
        if (sidebar && sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
        }
        // remove persisted flag
        try { localStorage.removeItem('sidebarCollapsed'); } catch(_){}
    } catch(e) {}

    // Keep sidebar links behavior simple (no auto-hide on click)
    document.querySelectorAll('.sidebar .nav-btn').forEach(link => {
        link.addEventListener('click', (e) => {
            // noop - sidebar stays fixed
        });
    });

    // User menu modal toggle (header)
    const userBtn = document.getElementById('userMenuBtn');
    const userModal = document.getElementById('userMenuModal');
    const logoutBtn = document.getElementById('logoutBtn');
    if (userBtn && userModal) {
        userBtn.addEventListener('click', (e) => {
            const isHidden = userModal.getAttribute('aria-hidden') === 'true';
            userModal.style.display = isHidden ? 'block' : 'none';
            userModal.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
        });

        // close when clicking outside
        document.addEventListener('click', (ev) => {
            if (!userModal || !userBtn) return;
            const target = ev.target;
            if (userModal.style.display === 'block' && !userModal.contains(target) && target !== userBtn) {
                userModal.style.display = 'none';
                userModal.setAttribute('aria-hidden', 'true');
            }
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            // perform logout by navigating to logout endpoint (expects POST ideally)
            // For simplicity, use a form POST to the logout route.
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/auth/logout';
            document.body.appendChild(f);
            f.submit();
        });
    }
});