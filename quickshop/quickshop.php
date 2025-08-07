<?php
$activePage = 'welcome';
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
$bgImagePath = 'img/bg2.png';
$bgImage = '../' . $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="quickshop-panel" style="background-image: url('<?php echo $bgImage; ?>');">
    <div class="quickshop-grid">
        <div class="quickshop-item"><img src="../img/default.png" alt="Plant 1"></div>
        <div class="quickshop-item"><img src="../img/default2.png" alt="Plant 2"></div>
        <div class="quickshop-item"><img src="../img/tarc1.png" alt="Plant 3"></div>
        <div class="quickshop-item"><img src="../img/sale.png" alt="Plant 4"></div>
        <div class="quickshop-item"><img src="../img/sale2aaaaaa.png" alt="Plant 5"></div>
        <div class="quickshop-item"><img src="../img/gold.png" alt="Plant 6"></div>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$pageCss = 'quickshop/quickshop.css';
$extraJs = '<script src="quickshop/quickshop.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>