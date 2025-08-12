<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$activePage = 'welcome';
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
require_once '../includes/db.php';
include_once '../includes/slot_helpers.php';
$userId = $_SESSION['user_id'];
$slotImage = get_slot_image($slotId, $userId);
$stmt = $db->prepare('SELECT 1 FROM user_plants WHERE user_id = ? AND slot_number = ?');
$stmt->execute([$userId, $slotId]);
$hasPlant = $stmt->fetchColumn() ? 1 : 0;
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="cs-slot-panel" data-slot-id="<?php echo $slotId; ?>" data-planted="<?php echo $hasPlant; ?>" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <img src="<?php echo $slotImage; ?>" alt="Slot <?php echo $slotId; ?>" id="cs-slot-image">
    <div id="cs-slot-actions">
        <?php if ($hasPlant): ?>
            <button class="cs-slot-btn" id="cs-slot-remove"><i class="fas fa-trash"></i><span>Remove</span></button>
        <?php else: ?>
            <button class="cs-slot-btn" id="cs-slot-shop"><i class="fas fa-store"></i><span>SHOP</span></button>
            <button class="cs-slot-btn" id="cs-slot-change"><i class="fas fa-random"></i><span>Change Plot Type</span></button>
        <?php endif; ?>
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