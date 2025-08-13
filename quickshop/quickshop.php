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
$stmt = $db->prepare('SELECT 1 FROM user_plants WHERE user_id = ? AND slot_number = ?');
$stmt->execute([$userId, $slotId]);
$hasPlant = $stmt->fetchColumn() ? 1 : 0;

$stmt = $db->prepare('SELECT id,name,image_plant,price,water_interval,feed_interval,water_times,feed_times,production FROM farm_items WHERE slot_type = ? AND active = 1');
$stmt->execute([$slotType]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Last helper info
$db->exec('CREATE TABLE IF NOT EXISTS user_last_helpers (
    owner_id INT PRIMARY KEY,
    helper_id INT NOT NULL,
    action ENUM("water","feed") NOT NULL,
    helped_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');
$helper = null;
$hstmt = $db->prepare('SELECT helper_id FROM user_last_helpers WHERE owner_id = ?');
$hstmt->execute([$userId]);
$hrow = $hstmt->fetch(PDO::FETCH_ASSOC);
if ($hrow) {
    $ustmt = $db->prepare('SELECT gallery FROM users WHERE id = ?');
    $ustmt->execute([$hrow['helper_id']]);
    $u = $ustmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $avatar = 'default-avatar.png';
        if (!empty($u['gallery'])) {
            $gal = array_filter(explode(',', $u['gallery']));
            if (!empty($gal)) {
                $candidate = 'uploads/' . $hrow['helper_id'] . '/' . trim($gal[0]);
                if (is_file(__DIR__ . '/../' . $candidate)) {
                    $avatar = $candidate;
                }
            }
        }
        $helper = ['id' => $hrow['helper_id'], 'avatar' => $avatar];
    }
}

$imagePrefix = $ajax ? '' : '../';

ob_start();
?>
<div id="quickshop-panel" data-slot-id="<?php echo $slotId; ?>" data-planted="<?php echo $hasPlant; ?>" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <?php if ($helper): ?>
    <div id="qs-helper-bar">
        <div class="qs-helper" data-user-id="<?= $helper['id']; ?>">
            <img src="<?= $imagePrefix . htmlspecialchars($helper['avatar']); ?>" alt="Helper">
        </div>
    </div>
    <?php endif; ?>
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
