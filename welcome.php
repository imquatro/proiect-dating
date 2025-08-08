<?php
$activePage = 'welcome';
ob_start();
include 'mini_profile.php';
include_once 'includes/slot_helpers.php';

$slotData = [];
$userId = $_SESSION['user_id'] ?? null;
if ($userId && isset($db)) {
    $stmt = $db->prepare("\n        SELECT ds.slot_number,\n               COALESCE(us.unlocked, ds.unlocked) AS unlocked,\n               COALESCE(us.required_level, ds.required_level) AS required_level\n        FROM default_slots ds\n        LEFT JOIN user_slots us\n            ON us.user_id = ? AND us.slot_number = ds.slot_number\n    ");
    $stmt->execute([$userId]);
    foreach ($stmt as $row) {
        $slotData[(int)$row['slot_number']] = $row;
    }
}
?>
<hr class="farm-divider">
<div class="farm-slots">
    <?php
$total_slots = 35;
$slots_per_row = 5;
$unaffectedSlots = [1,2,3,6,7,8];
for ($i = 0; $i < $total_slots; $i++) {
    if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
    $slot_id = $i + 1;
    $data = $slotData[$slot_id] ?? [];
    $isUnlocked = (!empty($data['unlocked'])) || in_array($slot_id, $unaffectedSlots);
    $classes = 'farm-slot' . ($isUnlocked ? '' : ' locked');
    $imgPath = get_slot_image($slot_id, $userId);
    $imgFullPath = __DIR__ . '/' . $imgPath;
    $imgSrc = $imgPath . '?v=' . (file_exists($imgFullPath) ? filemtime($imgFullPath) : time());
    echo '<div class="' . $classes . '" id="slot-' . $slot_id . '"><img src="' . $imgSrc . '" alt="slot">';
    if (!$isUnlocked) {
        if ($slot_id > $total_slots - 5) {
            echo '<div class="slot-overlay"><img src="img/gold.png" alt="Gold"></div>';
        } else {
            $required = htmlspecialchars($data['required_level'] ?? $slot_id);
            echo '<div class="slot-overlay">Level ' . $required . '</div>';
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
$pageCss = 'assets_css/welcome.css';
$extraJs = '<script src="assets_js/mini-profile.js"></script>'
         . '<script src="assets_js/farm-slots.js"></script>'
         . '<script src="changeslots/slot-panel.js"></script>'
         . '<script src="slotstype/slotstype.js"></script>';
include 'template.php';