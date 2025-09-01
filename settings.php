<?php
$activePage = 'settings';
session_start();
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/db.php';
    $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->fetchColumn() == 1;
}

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
                    <?php if ($isAdmin): ?>
                    <button class="sub-tab-btn" data-subtab="admin">Admin Panel</button>
                    <?php endif; ?>
                    <button class="sub-tab-btn" data-subtab="bank">Bank</button>
                    <button class="sub-tab-btn" data-subtab="leaderboard">Leaderboard</button>
                    <button class="sub-tab-btn" data-subtab="profile">Profile</button>
                    <button class="sub-tab-btn logout-init-btn" id="logoutBtn">Logout</button>
                </div>
                <div class="vip-subtab-content">
                    <?php if ($isAdmin): ?>
                    <div class="subtab-content" id="admin">
                        <div id="adminPanelContainer"></div>
                    </div>
                    <?php endif; ?>
                    <div class="subtab-content" id="bank">
                        <p style="color:#fff;">Bank coming soon</p>
                    </div>
                    <div class="subtab-content" id="leaderboard">
                        <p style="color:#fff;">Leaderboard coming soon</p>
                    </div>
                    <div class="subtab-content" id="profile">
                        <div id="profileContainer"></div>
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
$extraCss = $isAdmin ? ['farm_admin/admin-panel.css', 'assets_css/profile.css'] : ['assets_css/profile.css'];
$extraJs = ['assets_js/settings.js', 'assets_js/profile.js'];
if ($isAdmin) {
    $extraJs[] = 'farm_admin/admin-panel.js';
    $extraJs[] = 'farm_admin/achievements.js';
}

include 'template.php';
?>
