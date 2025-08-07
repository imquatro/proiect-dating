<?php
$activePage = 'welcome';
ob_start();
include 'mini_profile.php';
?>
<hr class="farm-divider">
<div class="farm-slots">
    <?php
    $total_slots = 35;
    $slots_per_row = 5;
    for ($i = 0; $i < $total_slots; $i++) {
        if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
        $slot_id = $i + 1;
        echo '<div class="farm-slot" id="slot-' . $slot_id . '"><img src="img/default.png" alt="slot"></div>';
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