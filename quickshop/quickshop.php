<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$activePage = 'welcome';
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
$ajax = isset($_GET['ajax']);
$imagePrefix = $ajax ? '' : '../';
$bgImagePath = 'img/bg2.png';
$bgImage = $imagePrefix . $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/slot_helpers.php';
$userId = $_SESSION['user_id'];
$slotType = get_slot_type($slotId, $userId);

// Check if slot already has a plant
$stmt = $db->prepare('SELECT 1 FROM user_plants WHERE user_id = ? AND slot_number = ?');
$stmt->execute([$userId, $slotId]);
$hasPlant = $stmt->fetchColumn() ? 1 : 0;

// Fetch items available for this slot type
$stmt = $db->prepare('SELECT id,name,image_plant,price,water_interval,feed_interval,water_times,feed_times,production FROM farm_items WHERE slot_type = ? AND active = 1');
$stmt->execute([$slotType]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine available slots of this type without plants
$slotQuery = $db->prepare(
    'SELECT ds.slot_number
     FROM default_slots ds
     LEFT JOIN user_slots us ON us.user_id = ? AND us.slot_number = ds.slot_number
     LEFT JOIN user_plants up ON up.user_id = ? AND up.slot_number = ds.slot_number
     WHERE COALESCE(us.slot_type, ds.slot_type) = ?
       AND COALESCE(us.unlocked, ds.unlocked) = 1
       AND up.slot_number IS NULL'
);
$slotQuery->execute([$userId, $userId, $slotType]);
$availableSlots = $slotQuery->fetchAll(PDO::FETCH_COLUMN);
$slotsCsv = implode(',', $availableSlots);

// VIP status for multiple planting
$vipStmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$vipStmt->execute([$userId]);
$isVip = (int)$vipStmt->fetchColumn();


ob_start();
?>
<div id="quickshop-panel" data-prefix="<?= htmlspecialchars($imagePrefix); ?>" data-slot-id="<?= $slotId; ?>" data-slot-type="<?= htmlspecialchars($slotType); ?>" data-planted="<?= $hasPlant; ?>" data-vip="<?= $isVip; ?>" style="background: url('<?= $bgImage; ?>') no-repeat center/cover;">
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
             data-production="<?= $item['production']; ?>"
             data-slots="<?= htmlspecialchars($slotsCsv); ?>">
            <img src="<?= $imagePrefix . htmlspecialchars($imagePlant); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
            <div class="qs-info">
                <span class="qs-price"><img src="<?= $imagePrefix; ?>img/money.png" alt="Money"> <?= $item['price']; ?></span>
                <span class="qs-details">Water: <?= $item['water_times']; ?>x | Feed: <?= $item['feed_times']; ?>x | Yield: <?= $item['production']; ?></span>
                <?php if (!empty($availableSlots)): ?>
                <select class="qs-count">
                    <?php for ($i = 1; $i <= count($availableSlots); $i++): ?>
                    <option value="<?= $i; ?>"><?= $i; ?></option>
                    <?php endfor; ?>
                </select>
                <?php endif; ?>
                <button class="qs-buy">BUY/USE</button>
            </div>
        </div>
        <?php endforeach; ?>
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