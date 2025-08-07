<?php
$activePage = 'welcome';
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
include_once '../includes/slot_helpers.php';
$slotImage = get_slot_image($slotId);
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="cs-slot-panel" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <img src="<?php echo $slotImage; ?>" alt="Slot <?php echo $slotId; ?>" id="cs-slot-image">
    <div id="cs-slot-actions">
        <button class="cs-slot-btn" id="cs-slot-shop"><i class="fas fa-store"></i><span>SHOP</span></button>
        <button class="cs-slot-btn" id="cs-slot-change"><i class="fas fa-random"></i><span>Change Plot Type</span></button>
        <button class="cs-slot-btn" id="cs-slot-swap"><i class="fas fa-exchange-alt"></i><span>Swap Plots</span></button>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$pageCss = 'changeslots/slot-panel.css';
$extraJs = '<script src="changeslots/slot-panel.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>