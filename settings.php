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
                    <button class="sub-tab-btn" data-subtab="helperi">Helperi</button>
                    <button class="sub-tab-btn" data-subtab="loading">Loading Style</button>
                </div>
                <div class="logout-btn-container">
                    <button class="sub-tab-btn logout-init-btn" id="logoutBtn">Logout</button>
                </div>
                <div class="vip-subtab-content">
                    <?php if ($isAdmin): ?>
                    <div class="subtab-content" id="admin">
                        <div id="adminPanelContainer"></div>
                    </div>
                    <?php endif; ?>
                    <div class="subtab-content" id="bank">
                        <img src="img/bank.png" alt="Bank" class="bank-header-img">
                        <h3>Welcome to our bank!</h3>
                        <p>What can we do for you?</p>
                        <div class="bank-buttons">
                            <button class="bank-btn active" data-banktab="deposit">Deposit</button>
                            <button class="bank-btn" data-banktab="loan">Loan</button>
                            <button class="bank-btn" data-banktab="account">My Account</button>
                            <button class="bank-btn" data-banktab="history">History</button>
                        </div>
                        <div class="bank-tab active" id="bank-deposit">
                            <div class="deposit-form">
                                <p>You can deposit <strong>1,000,000</strong> coins. Interest increases by 1000 coins per hour.</p>
                                <p id="depositLimit" class="deposit-limit"></p>
                                <label>Duration:
                                    <select id="depositHours"></select>
                                </label>
                                <div id="depositPreview" class="deposit-preview"></div>
                                <button id="depositBtn" class="bank-action-btn">Deposit</button>
                                <div id="depositMessage" class="deposit-message"></div>
                            </div>
                            <div id="activeDeposits"></div>
                        </div>
                        <div class="bank-tab" id="bank-loan">
                            <div class="loan-form">
                                <p>You can borrow up to <strong>10,000</strong> coins. Payback is twice the borrowed amount and 70% of barn sales go toward repayment.</p>
                                <p id="loanLimit" class="deposit-limit"></p>
                                <label>Amount:
                                    <input type="range" id="loanAmount" min="1000" max="10000" step="1000">
                                </label>
                                <div id="loanPreview" class="deposit-preview"></div>
                                <button id="loanBtn" class="bank-action-btn">Borrow</button>
                                <div id="loanMessage" class="deposit-message"></div>
                            </div>
                            <div id="activeLoans"></div>
                        </div>
                        <div class="bank-tab" id="bank-account">
                            <div id="accountDeposits"></div>
                        </div>
                        <div class="bank-tab" id="bank-history">
                            <div id="historyDeposits"></div>
                            <div id="historyLoans"></div>
                        </div>
                    </div>
                    <div class="subtab-content" id="leaderboard">
                        <p style="color:#fff;">Leaderboard coming soon</p>
                    </div>
                    <div class="subtab-content" id="profile">
                        <div id="profileContainer"></div>
                    </div>
                    <div class="subtab-content" id="helperi">
                        <div id="helpersList"></div>
                        <div id="helperSettings"></div>
                    </div>
                    <div class="subtab-content" id="loading">
                        <div class="loading-redirect-container">
                            <div class="loading-redirect-icon">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                            <h3>Loading Style Preferences</h3>
                            <p>Customize your navigation loading animation</p>
                            <button class="loading-redirect-btn" onclick="window.location.href='loading_settings.php'">
                                <i class="fas fa-palette"></i> Choose Loading Style
                            </button>
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
$extraCss = $isAdmin ? ['farm_admin/admin-panel.css', 'assets_css/profile.css', 'assets_css/bank.css', 'assets_css/helperi.css'] : ['assets_css/profile.css', 'assets_css/bank.css', 'assets_css/helperi.css'];
$extraJs = $isAdmin
    ? ['farm_admin/admin-panel.js', 'farm_admin/achievements.js', 'assets_js/profile.js', 'assets_js/bank.js', 'assets_js/helpers.js', 'assets_js/settings.js']
    : ['assets_js/profile.js', 'assets_js/bank.js', 'assets_js/helpers.js', 'assets_js/settings.js'];

include 'template.php';
?>
