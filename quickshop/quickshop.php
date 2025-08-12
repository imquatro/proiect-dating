<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$activePage = 'welcome';
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);

require_once '../includes/db.php';
include_once '../includes/slot_helpers.php';
$userId = $_SESSION['user_id'];
$slotType = get_slot_type($slotId, $userId);

$stmt = $db->prepare('SELECT id,name,image_plant,price,water_interval,feed_interval,water_times,feed_times,production FROM farm_items WHERE slot_type = ? AND active = 1');
$stmt->execute([$slotType]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine current plant in slot
$plantStmt = $db->prepare('SELECT f.image_plant, f.name FROM user_plants up JOIN farm_items f ON f.id = up.item_id WHERE up.user_id = ? AND up.slot_number = ?');
$plantStmt->execute([$userId, $slotId]);
$currentPlant = $plantStmt->fetch(PDO::FETCH_ASSOC);
$hasPlant = $currentPlant ? true : false;

$slotImage = slot_image_from_type($slotType);
if (strpos($slotImage, 'img/') !== 0) {
    $slotImage = 'img/' . ltrim($slotImage, '/');
}
$plantImage = $hasPlant ? $currentPlant['image_plant'] : '';
if ($plantImage && strpos($plantImage, 'img/') !== 0) {
    $plantImage = 'img/' . ltrim($plantImage, '/');
}
$imagePrefix = $ajax ? '' : '../';

ob_start();
?>
<div id="quickshop-panel" data-slot-id="<?php echo $slotId; ?>"<?php if ($hasPlant) echo ' data-planted="1"'; ?> style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div class="qs-slot-view">
        <img src="<?= $imagePrefix . htmlspecialchars($slotImage); ?>" class="qs-slot-base" alt="Slot">
        <?php if ($hasPlant): ?>
        <img src="<?= $imagePrefix . htmlspecialchars($plantImage); ?>" class="qs-slot-plant" alt="<?= htmlspecialchars($currentPlant['name']); ?>">
        <?php endif; ?>
    </div>
    <?php if ($hasPlant): ?>
        <button class="qs-remove">REMOVE</button>
    <?php else: ?>
    <div class="quickshop-grid">
        <?php foreach ($items as $item):
            $imagePlant = $item['image_plant'];
            if (strpos($imagePlant, 'img/') !== 0) {
                $imagePlant = 'img/' . ltrim($imagePlant, '/');
            }
        ?>
        <div class="quickshop-item"
             data-item-id="<?= $item['id']; ?>"
             data-price="<?= $item['price']; ?>"
             data-water="<?= $item['water_interval']; ?>"
             data-feed="<?= $item['feed_interval']; ?>"
             data-water-times="<?= $item['water_times']; ?>"
             data-feed-times="<?= $item['feed_times']; ?>"
             data-production="<?= $item['production']; ?>">
            <img src="<?= $imagePrefix . htmlspecialchars($imagePlant); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
            <div class="qs-info">
                <span class="qs-price"><?= $item['price']; ?></span>
                <button class="qs-buy">BUY/USE</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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