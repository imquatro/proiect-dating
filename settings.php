<?php
$activePage = 'settings';

ob_start();
?>
<div class="vip-container">
    <div id="settingsPanel" class="vip-panel">
        <div class="vip-tabs">
            <button class="tab-btn active" data-tab="settings">Settings</button>
        </div>
        <div class="vip-tab-content">
            <div class="tab-content active" id="settings">
                <div class="vip-sub-tabs">
                    <button class="sub-tab-btn" data-subtab="admin">Admin Panel</button>
                    <button class="sub-tab-btn" data-subtab="bank">Bank</button>
                    <button class="sub-tab-btn" data-subtab="leaderboard">Leaderboard</button>
                    <button class="sub-tab-btn" onclick="location.href='profile.php';">Profile</button>
                    <button class="sub-tab-btn logout-init-btn" id="logoutBtn">Logout</button>
                </div>
                <div class="vip-subtab-content">
                </div>
                <div class="vip-subtab-content">
                    <div class="subtab-content" id="admin">
                        <button class="apply-frame-btn" onclick="location.href='farm_admin/panel.php';">Go to Admin Panel</button>
                    </div>
                    <div class="subtab-content" id="bank">
                        <p style="color:#fff;">Bank coming soon</p>
                    </div>
                    <div class="subtab-content" id="leaderboard">
                        <p style="color:#fff;">Leaderboard coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="logoutOverlay" class="logout-overlay" style="display:none;">
    <div class="logout-card">
        <p>Are you sure you want to log out?</p>
        <button id="confirmLogout" class="apply-frame-btn">Logout</button>
    </div>
</div>
<?php
$content = ob_get_clean();

$pageCss = 'assets_css/settings.css';
$extraJs = '<script src="assets_js/settings.js"></script>';

include 'template.php';
?>