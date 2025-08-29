<?php
$activePage = 'vip';
ob_start();
$frameDir = 'img/vip_frames';
$frames = array_filter(glob($frameDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE));
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
                        <div class="vip-frame-grid">
                            <?php foreach ($frames as $img): ?>
                            <div class="vip-frame-item">
                                <img src="<?= htmlspecialchars($img) ?>" alt="VIP Frame">
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