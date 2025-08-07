<?php
$activePage = 'welcome';
ob_start();
include 'mini_profile.php';

$slotData = [];
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    if (isset($db)) {
        $stmt = $db->prepare("SELECT slot_number, unlocked, required_level FROM user_slots WHERE user_id = ?");
        $stmt->execute([$userId]);
        foreach ($stmt as $row) {
            $slotData[(int)$row['slot_number']] = $row;
        }
    }
}
?>
<hr class="farm-divider">
<div class="farm-slots">
    <?php
    $total_slots = 35;
    $slots_per_row = 5;
    $unaffectedSlots = [1,2,3,6,7,8,31,32,33,34,35];
    for ($i = 0; $i < $total_slots; $i++) {
        if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
        $slot_id = $i + 1;
        $data = $slotData[$slot_id] ?? [];
        $isUnlocked = (!empty($data['unlocked'])) || in_array($slot_id, $unaffectedSlots);
        $classes = 'farm-slot' . ($isUnlocked ? '' : ' locked');
        echo '<div class="' . $classes . '" id="slot-' . $slot_id . '"><img src="img/default.png" alt="slot">';
        if (!$isUnlocked) {
            $required = htmlspecialchars($data['required_level'] ?? $slot_id);
            echo '<div class="slot-overlay">Nivel ' . $required . '</div>';
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
