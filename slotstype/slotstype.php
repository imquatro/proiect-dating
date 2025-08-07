<?php
$activePage = 'welcome';
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="st-slotstype-panel" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="st-slotstype-list">
        <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="st-slotstype-item">Type <?php echo $i; ?></div>
        <?php endfor; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$pageCss = 'slotstype/slotstype.css';
$extraJs = '<script src="slotstype/slotstype.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>