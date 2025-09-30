document.addEventListener('DOMContentLoaded', function () {
    function initTabs(container, btnSelector, contentSelector, attr) {
        const btns = container.querySelectorAll(btnSelector);
        const contents = container.querySelectorAll(contentSelector);
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                btns.forEach(b => b.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                const target = btn.getAttribute(attr);
                const el = container.querySelector('#' + target);
                if (el) el.classList.add('active');
            });
        });
    }
    const panel = document.getElementById('settingsPanel');
    if (!panel) return;
    initTabs(panel, '.tab-btn', '.tab-content', 'data-tab');
    panel.querySelectorAll('.vip-sub-tabs').forEach(sub => {
        initTabs(sub.parentElement, '.sub-tab-btn[data-subtab]', '.subtab-content', 'data-subtab');
    });
    const adminContainer = document.getElementById('adminPanelContainer');
    if (adminContainer) {
        fetch('farm_admin/panel.php?ajax=1', { credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => {
                adminContainer.innerHTML = html;
                const panelEl = adminContainer.querySelector('#fa-admin-panel');
                if (panelEl) {
                    const boot = () => {
                        if (typeof initAdminPanel === 'function') {
                            initAdminPanel(panelEl);
                        } else {
                            setTimeout(boot, 50);
                        }
                    };
                    boot();
                }
            });
    }
    const profileContainer = document.getElementById('profileContainer');
    if (profileContainer) {
        fetch('profile.php?ajax=1')
            .then(res => res.text())
            .then(html => {
                profileContainer.innerHTML = html;
                if (typeof initProfile === 'function') initProfile();
            });
    }
    const logoutBtn = document.getElementById('logoutBtn');
    const overlay = document.getElementById('logoutOverlay');
    const confirmLogout = document.getElementById('confirmLogout');
    if (logoutBtn && overlay && confirmLogout) {
        logoutBtn.addEventListener('click', () => {
            overlay.style.display = 'flex';
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });
        confirmLogout.addEventListener('click', () => {
            window.location.href = 'logout.php';
        });
    }
});
