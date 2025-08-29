<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$activePage = 'vip';
ob_start();
$isVip = false;
$currentFrame = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT vip, vip_frame FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $isVip = !empty($u['vip']);
        $currentFrame = $u['vip_frame'] ?? '';
    }
}
$frameDir = 'img/vip_frames';
$frames = array_map('basename', array_filter(glob($frameDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));
?>
<div class="vip-container">
    <div id="vipPanel" class="vip-panel">
        <div class="vip-tabs">
            <button class="tab-btn active" data-tab="vip">VIP</button>
        </div>
        <div class="vip-tab-content">
            <div class="tab-content active" id="vip">
                <div class="vip-sub-tabs">
                    <button class="sub-tab-btn active" data-subtab="frames">Frames</button>
                    <button class="sub-tab-btn" data-subtab="cards">Cards</button>
                </div>
                <div class="vip-subtab-content">
                    <div class="subtab-content active" id="frames">
                        <?php if (!$isVip): ?>
                        <div class="mini-card vip-warning">You need VIP to use frames</div>
                        <?php else: ?>
                            <?php if ($currentFrame): ?>
                                <button id="removeFrameBtn" class="remove-frame">Remove Frame</button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="vip-frame-grid">
                            <?php foreach ($frames as $img): ?>
                            <div class="vip-frame-item">
                                <img src="<?= htmlspecialchars($frameDir . '/' . $img) ?>" alt="VIP Frame">
                                <?php if ($isVip): ?>
                                <button class="apply-frame-btn" data-frame="<?= htmlspecialchars($img) ?>">Apply</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="subtab-content" id="cards">
                        <p style="color:#fff;">Coming soon</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/vip.css';
$extraJs = '<script src="assets_js/vip.js"></script>';
include 'template.php';
?>