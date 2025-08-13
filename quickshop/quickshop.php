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

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/slot_helpers.php';
$userId = $_SESSION['user_id'];
$slotType = get_slot_type($slotId, $userId);

// Check if slot already has a plant
$stmt = $db->prepare('SELECT item_id FROM user_plants WHERE user_id = ? AND slot_number = ?');
$stmt->execute([$userId, $slotId]);
$plantRow = $stmt->fetch(PDO::FETCH_ASSOC);
$hasPlant = $plantRow ? 1 : 0;

$stmt = $db->prepare('SELECT id,name,image_plant,price,water_interval,feed_interval,water_times,feed_times,production FROM farm_items WHERE slot_type = ? AND active = 1');
$stmt->execute([$slotType]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$progress = null;
$helpers = [];
$plantImage = null;
if ($hasPlant) {
    $pst = $db->prepare('SELECT f.water_times, f.feed_times, f.image_plant FROM farm_items f WHERE f.id = ?');
    $pst->execute([$plantRow['item_id']]);
    $plant = $pst->fetch(PDO::FETCH_ASSOC);
    if ($plant) {
        $plantImage = $plant['image_plant'];
    }

    $sst = $db->prepare('SELECT water_remaining, feed_remaining FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
    $sst->execute([$userId, $slotId]);
    $state = $sst->fetch(PDO::FETCH_ASSOC);

    if ($plant && $state) {
        $progress = [
            'water_done' => $plant['water_times'] - $state['water_remaining'],
            'water_total' => $plant['water_times'],
            'feed_done' => $plant['feed_times'] - $state['feed_remaining'],
            'feed_total' => $plant['feed_times']
        ];
    }

    $db->exec('CREATE TABLE IF NOT EXISTS slot_helpers (
        owner_id INT NOT NULL,
        slot_number INT NOT NULL,
        helper_id INT NOT NULL,
        clicks INT NOT NULL DEFAULT 1,
        PRIMARY KEY (owner_id, slot_number, helper_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

    $hstmt = $db->prepare('SELECT h.helper_id, h.clicks, u.gallery FROM slot_helpers h JOIN users u ON u.id = h.helper_id WHERE h.owner_id = ? AND h.slot_number = ? ORDER BY h.clicks DESC');
    $hstmt->execute([$userId, $slotId]);
    while ($row = $hstmt->fetch(PDO::FETCH_ASSOC)) {
        $avatar = 'default-avatar.png';
        if (!empty($row['gallery'])) {
            $gal = array_filter(explode(',', $row['gallery']));
            if (!empty($gal)) {
                $candidate = 'uploads/' . $row['helper_id'] . '/' . trim($gal[0]);
                if (is_file(__DIR__ . '/../' . $candidate)) {
                    $avatar = $candidate;
                }
            }
        }
        $helpers[] = ['id' => $row['helper_id'], 'avatar' => $avatar, 'clicks' => $row['clicks']];
    }
}

$imagePrefix = $ajax ? '' : '../';

ob_start();
?>
<div id="quickshop-panel" data-slot-id="<?php echo $slotId; ?>" data-planted="<?php echo $hasPlant; ?>" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="qs-slot-preview">
        <?php if ($plantImage): ?>
        <img src="<?= $imagePrefix . htmlspecialchars(strpos($plantImage, 'img/') === 0 ? $plantImage : 'img/' . ltrim($plantImage, '/')); ?>" alt="Plant">
        <?php endif; ?>
    </div>
    <div id="qs-helper-panel">
        <div class="qs-progress">
            <div class="qs-progress-item">Water <?= $progress ? $progress['water_done'] : 0; ?>/<?= $progress ? $progress['water_total'] : 0; ?></div>
            <div class="qs-progress-item">Feed <?= $progress ? $progress['feed_done'] : 0; ?>/<?= $progress ? $progress['feed_total'] : 0; ?></div>
        </div>
        <div class="qs-helper-grid">
            <?php foreach ($helpers as $h): ?>
            <div class="qs-helper" data-user-id="<?= $h['id']; ?>">
                <img src="<?= $imagePrefix . htmlspecialchars($h['avatar']); ?>" alt="Helper">
                <span class="qs-count"><?= $h['clicks']; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
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
