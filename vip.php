<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$activePage = 'vip';
ob_start();
$isVip = false;
$currentFrame = '';
$currentCard = '';
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT vip, vip_frame, vip_card FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $isVip = !empty($u['vip']);
        $currentFrame = $u['vip_frame'] ?? '';
        $currentCard = $u['vip_card'] ?? '';
    }
}
$frameDir = 'img/vip_frames';
$frames = array_map('basename', array_filter(glob($frameDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));
$cardDir = 'img/vip_cards';
$cards = array_map('basename', array_filter(glob($cardDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));
?>
<div class="vip-container">
    <div id="vipPanel" class="vip-panel">
        <div class="vip-tabs">
            <button class="tab-btn active" data-tab="vip">VIP</button>
        </div>
        <div class="vip-tab-content">
            <div class="tab-content active" id="vip">
                <div class="vip-sub-tabs">
                    <button class="sub-tab-btn active" data-subtab="benefits">Benefits</button>
                    <button class="sub-tab-btn" data-subtab="frames">Frames</button>
                    <button class="sub-tab-btn" data-subtab="cards">Cards</button>
                </div>
                <div class="vip-subtab-content">
                    <div class="subtab-content active" id="benefits">
                        <h1 class="vip-benefit-title">Unlock the VIP Experience</h1>
                        <p class="vip-benefit-text">VIP status unlocks in-game advantages and dazzling style:</p>
                        <ul class="vip-benefit-list">
                            <li>Gain 5 extra farming slots available only to VIPs</li>
                            <li>Plant multiple crops at once for faster progress</li>
                            <li>Harvest several plots in one action and earn 8 XP per plot</li>
                            <li>Remove items from multiple plots simultaneously</li>
                            <li>Receive 10 XP for each bulk sale of 1,000 items</li>
                            <li>Hold up to 5 simultaneous bank deposits</li>
                            <li>Unlock exclusive profile frames and cards to set on your farm profile</li>
                            <li>Shine with a shimmering name like <span class="gold-shimmer">username</span></li>
                        </ul>
                    </div>
                    <div class="subtab-content" id="frames">
                        <?php if (!$isVip): ?>
                        <div class="mini-card vip-warning">You need VIP to use frames and cards</div>
                        <?php else: ?>
                            <?php if ($currentFrame): ?>
                                <button id="removeFrameBtn" class="remove-frame">Remove Frame</button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="vip-frame-grid">
                            <?php foreach ($frames as $img): ?>
                            <div class="vip-frame-item<?= $currentFrame === $img ? ' selected' : '' ?>" data-frame="<?= htmlspecialchars($frameDir . '/' . $img) ?>">
                                <img src="<?= htmlspecialchars($frameDir . '/' . $img) ?>" alt="VIP Frame">
                                <?php if ($isVip): ?>
                                <button class="apply-frame-btn" data-frame="<?= htmlspecialchars($img) ?>">Apply</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="subtab-content" id="cards">
                        <?php if (!$isVip): ?>
                        <div class="mini-card vip-warning">You need VIP to use frames and cards</div>
                        <?php else: ?>
                            <?php if ($currentCard): ?>
                                <button id="removeCardBtn" class="remove-frame">Remove Card</button>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="vip-card-grid">
                            <?php foreach ($cards as $img): ?>
                            <div class="vip-card-item<?= $currentCard === $img ? ' selected' : '' ?>" data-card="<?= htmlspecialchars($cardDir . '/' . $img) ?>">
                                <img src="<?= htmlspecialchars($cardDir . '/' . $img) ?>" alt="VIP Card">
                                <?php if ($isVip): ?>
                                <button class="apply-card-btn" data-card="<?= htmlspecialchars($img) ?>">Apply</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="vipPreview" class="vip-preview">
    <?php
    $mini_profile_config = [
        'show_helpers' => false,
        'show_achievements' => false,
        'show_helper_effect' => false
    ];
    include 'mini_profile.php';
    ?>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/vip.css';
$extraCss = ['assets_css/mini-profile.css'];
$extraJs = '<script src="assets_js/vip.js"></script>';
include 'template.php';
?>
