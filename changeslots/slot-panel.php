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

// Base slot image
$slotType = get_slot_type($slotId, $userId);
$basePath = slot_image_from_type($slotType);
if (strpos($basePath, 'img/') !== 0) {
    $basePath = 'img/' . ltrim($basePath, '/');
}
$slotImage = $basePath . '?v=' . filemtime(__DIR__ . '/../' . $basePath);

// Plant details and remaining tasks
$stmt = $db->prepare('SELECT f.image_plant, f.water_times, f.feed_times, uss.water_remaining, uss.feed_remaining
                      FROM user_plants up
                      JOIN farm_items f ON f.id = up.item_id
                      LEFT JOIN user_slot_states uss ON uss.user_id = up.user_id AND uss.slot_number = up.slot_number
                      WHERE up.user_id = ? AND up.slot_number = ?');
$stmt->execute([$userId, $slotId]);
$plantRow = $stmt->fetch(PDO::FETCH_ASSOC);
$hasPlant = $plantRow ? 1 : 0;
$plantImage = '';
$waterTimes = $feedTimes = $waterRemaining = $feedRemaining = 0;
if ($hasPlant) {
    $plantPath = $plantRow['image_plant'];
    if (strpos($plantPath, 'img/') !== 0) {
        $plantPath = 'img/' . ltrim($plantPath, '/');
    }
    $plantImage = $plantPath . '?v=' . filemtime(__DIR__ . '/../' . $plantPath);
    $waterTimes = (int)$plantRow['water_times'];
    $feedTimes = (int)$plantRow['feed_times'];
    $waterRemaining = $plantRow['water_remaining'] !== null ? (int)$plantRow['water_remaining'] : $waterTimes;
    $feedRemaining = $plantRow['feed_remaining'] !== null ? (int)$plantRow['feed_remaining'] : $feedTimes;
    $waterDone = $waterTimes - $waterRemaining;
    $feedDone = $feedTimes - $feedRemaining;
}

$helpers = [];
if ($hasPlant) {
    $tableCheck = $db->query("SHOW TABLES LIKE 'slot_helpers'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        $hstmt = $db->prepare('SELECT sh.helper_id, sh.water_clicks, sh.feed_clicks, sh.last_action_at, u.gallery
                           FROM slot_helpers sh
                           JOIN users u ON u.id = sh.helper_id
                           WHERE sh.owner_id = ? AND sh.slot_number = ?
                           ORDER BY sh.last_action_at DESC');
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
            $helpers[] = [
                'avatar' => $avatar,
                'count' => (int)$row['water_clicks'] + (int)$row['feed_clicks']
            ];
        }
    }
}

$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);

$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="cs-slot-panel" data-slot-id="<?php echo $slotId; ?>" data-planted="<?php echo $hasPlant; ?>" style="background:
 url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div class="cs-image-wrapper">
        <img src="<?php echo $slotImage; ?>" alt="Slot <?php echo $slotId; ?>" id="cs-slot-image">
        <?php if ($hasPlant && $plantImage): ?>
            <img src="<?php echo $plantImage; ?>" alt="Plant" id="cs-plant-image">
        <?php endif; ?>
    </div>
    <?php if ($hasPlant): ?>
        <div id="cs-slot-details">
            <?php if ($waterTimes > 0): ?>
                <div class="cs-detail">Waterings: <?php echo $waterDone; ?>/<?php echo $waterTimes; ?> (<?php echo $waterRemaining; ?> left)</div>
            <?php endif; ?>
            <?php if ($feedTimes > 0): ?>
                <div class="cs-detail">Feedings: <?php echo $feedDone; ?>/<?php echo $feedTimes; ?> (<?php echo $feedRemaining; ?> left)</div>
            <?php endif; ?>
        </div>
        <div id="cs-helper-container">
            <?php foreach ($helpers as $h): ?>
                <div class="cs-helper-entry">
                    <img src="<?php echo htmlspecialchars($h['avatar']); ?>" alt="Helper">
                    <span class="cs-helper-count"><?php echo $h['count']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
