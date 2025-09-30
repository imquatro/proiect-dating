<?php
$activePage = 'welcome';
$mini_profile_config = ['show_helpers' => true];
ob_start();
include 'mini_profile.php';
include_once 'includes/slot_helpers.php';
require_once 'includes/helper_actions.php';

$slotData = [];
$userId = $_SESSION['user_id'] ?? null;
$userLevel = 1;
$isVip = 0;
$helperSummary = null;
if ($userId && isset($db)) {
    $stmt = $db->prepare("
        SELECT ds.slot_number,
               COALESCE(us.unlocked, ds.unlocked) AS unlocked,
               COALESCE(us.required_level, ds.required_level) AS required_level
        FROM default_slots ds
        LEFT JOIN user_slots us
            ON us.user_id = ? AND us.slot_number = ds.slot_number
    ");
    $stmt->execute([$userId]);
    foreach ($stmt as $row) {
        $slotData[(int)$row['slot_number']] = $row;
    }
    $lvlStmt = $db->prepare('SELECT level, vip FROM users WHERE id = ?');
    $lvlStmt->execute([$userId]);
    $userRow = $lvlStmt->fetch(PDO::FETCH_ASSOC);
    $userLevel = (int)($userRow['level'] ?? 1);
    $isVip = !empty($userRow['vip']);
    $helperSummary = process_helper_actions($userId);
    if ($helperSummary && (
        $helperSummary['waterUsed'] ||
        $helperSummary['feedUsed'] ||
        $helperSummary['harvestUsed']
    )) {
        $cookieName = 'helper_summary_' . $userId;
        $summaryHash = date('Y-m-d') . ':'
            . $helperSummary['waterUsed'] . '-'
            . $helperSummary['feedUsed'] . '-'
            . $helperSummary['harvestUsed'];
        if (($_COOKIE[$cookieName] ?? '') === $summaryHash) {
            $helperSummary = null;
        } else {
            setcookie($cookieName, $summaryHash, time() + 86400 * 30, '/');
        }
    }
}
?>
<?php if ($helperSummary && ($helperSummary['waterUsed'] || $helperSummary['feedUsed'] || $helperSummary['harvestUsed'])): ?>
<div id="helper-summary" class="helper-overlay">
    <div class="applied-helper-card">
        <img src="<?= htmlspecialchars($helperSummary['helper']['image']); ?>" alt="helper">
        <div>
            <p>Watered <?= $helperSummary['waterUsed']; ?> / <?= $helperSummary['waterLimit']; ?></p>
            <p>Fed <?= $helperSummary['feedUsed']; ?> / <?= $helperSummary['feedLimit']; ?></p>
            <p>Harvested <?= $helperSummary['harvestUsed']; ?> / <?= $helperSummary['harvestLimit']; ?></p>
        </div>
    </div>
</div>
<script>
document.getElementById('helper-summary').addEventListener('click', function(e){
    if (e.target === this) this.remove();
});
</script>
<?php endif; ?>
<hr class="farm-divider">
<div class="farm-slots">
    <?php
$total_slots = 35;
$slots_per_row = 5;
for ($i = 0; $i < $total_slots; $i++) {
    if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
    $slot_id = $i + 1;
    $data = $slotData[$slot_id] ?? ['unlocked' => 0, 'required_level' => 0];
    $required = get_slot_required_level($slot_id);
    if ($data['required_level'] != $required) {
        $db->prepare('INSERT INTO user_slots (user_id, slot_number, required_level)
                       VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE required_level = VALUES(required_level)')
           ->execute([$userId, $slot_id, $required]);
        $data['required_level'] = $required;
    }
    $isUnlocked = !empty($data['unlocked']);
    if (!$isUnlocked) {
        if ($slot_id <= $total_slots - 5) {
            if ($required > 0 && $userLevel >= $required) {
                $db->prepare('INSERT INTO user_slots (user_id, slot_number, unlocked)
                               VALUES (?, ?, 1)
                               ON DUPLICATE KEY UPDATE unlocked = 1')
                   ->execute([$userId, $slot_id]);
                $isUnlocked = true;
            }
        } elseif ($isVip) {
            $db->prepare('INSERT INTO user_slots (user_id, slot_number, unlocked)
                           VALUES (?, ?, 1)
                           ON DUPLICATE KEY UPDATE unlocked = 1')
               ->execute([$userId, $slot_id]);
            $isUnlocked = true;
        }
    }
    $classes = 'farm-slot' . ($isUnlocked ? '' : ' locked');
    $baseImg = get_slot_image($slot_id, $userId);
    $imgFullPath = __DIR__ . '/' . $baseImg;
    $imgSrc = $baseImg . '?v=' . (file_exists($imgFullPath) ? filemtime($imgFullPath) : time());
    echo '<div class="' . $classes . '" id="slot-' . $slot_id . '">';
    echo '<img class="slot-base" src="' . $imgSrc . '" alt="slot">';
    echo '<img class="slot-item" alt="item" style="display:none;">';
    echo '<div class="slot-action"></div>';
    echo '<div class="slot-timer"></div>';
    if (!$isUnlocked) {
        if ($slot_id > $total_slots - 5) {
            echo '<div class="slot-overlay"><img src="img/gold.png" alt="Gold"></div>';
        } else {
            $requiredHtml = htmlspecialchars($required);
            echo '<div class="slot-overlay">Level ' . $requiredHtml . '</div>';
        }
    }
    echo '</div>';
    if ($i % $slots_per_row === $slots_per_row - 1) echo '</div>';
}
if ($total_slots % $slots_per_row !== 0) echo '</div>';
    ?>
</div>
<?php
$content = ob_get_clean();
$pageCss = '';
$extraCss = [
    'assets_css/mini-profile.css',
    'assets_css/farm-slots.css',
    'changeslots/slot-panel.css',
    'quickshop/quickshop.css',
    'slotstype/slotstype.css',
];
$extraJs = '<script src="assets_js/mini-profile.js"></script>'
         . '<script src="assets_js/farm-slots.js"></script>'
         . '<script src="assets_js/slot-items.js"></script>'
         . '<script src="changeslots/slot-panel.js"></script>'
         . '<script src="slotstype/slotstype.js"></script>'
         . '<script src="assets_js/helper-effect.js"></script>';
include 'template.php';
?>
