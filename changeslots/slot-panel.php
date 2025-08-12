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

// Base slot image based on slot type
$basePath = slot_image_from_type(get_slot_type($slotId, $userId));
$baseImage = $basePath . '?v=' . filemtime(__DIR__ . '/../' . $basePath);

// Check if a plant exists in this slot and get its image
$stmt = $db->prepare(
    'SELECT f.image_plant FROM user_plants up JOIN farm_items f ON f.id = up.item_id
     WHERE up.user_id = ? AND up.slot_number = ?'
);
$stmt->execute([$userId, $slotId]);
$plantPath = $stmt->fetchColumn();
$hasPlant = $plantPath ? 1 : 0;
if ($plantPath && strpos($plantPath, 'img/') !== 0) {
    $plantPath = 'img/' . ltrim($plantPath, '/');
}
$plantImage = $hasPlant ? $plantPath . '?v=' . filemtime(__DIR__ . '/../' . $plantPath) : '';
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="cs-slot-panel" data-slot-id="<?php echo $slotId; ?>" data-planted="<?php echo $hasPlant; ?>" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="cs-slot-image-wrapper">
        <img src="<?php echo $baseImage; ?>" alt="Slot <?php echo $slotId; ?>" id="cs-slot-image">
        <?php if ($hasPlant): ?>
            <img src="<?php echo $plantImage; ?>" alt="Plant" id="cs-slot-plant">
        <?php endif; ?>
    </div>
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