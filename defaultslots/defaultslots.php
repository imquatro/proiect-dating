<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
$activePage = 'welcome';
require_once '../includes/db.php';
require_once '../includes/slot_helpers.php';

$userId = $_SESSION['user_id'];
$stmt = $db->prepare("\n    SELECT ds.slot_number,\n           COALESCE(us.unlocked, ds.unlocked) AS unlocked,\n           COALESCE(us.required_level, ds.required_level) AS required_level\n    FROM default_slots ds\n    LEFT JOIN user_slots us\n        ON us.user_id = ? AND us.slot_number = ds.slot_number\n    ORDER BY ds.slot_number\n");
$stmt->execute([$userId]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
$slotData = [];
foreach ($slots as $slot) {
    $slotData[(int)$slot['slot_number']] = $slot;
}
$bgImagePath = '../img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../img/bg2.png');
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="ds-slot-panel" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="ds-slot-menu">
        <?php for ($i = 1; $i <= 10; $i++):
            $data = $slotData[$i] ?? ['unlocked' => 0, 'required_level' => 0];
            $classes = 'ds-slot';
            if ($i === 1) { $classes .= ' active'; }
            if (!empty($data['unlocked'])) { $classes .= ' open'; } else { $classes .= ' locked'; }
        ?>
        <div class="<?php echo $classes; ?>" data-slot="<?php echo $i; ?>">
            <img src="../<?php echo get_slot_image($i, $userId); ?>" class="slot-img" alt="Slot <?php echo $i; ?>">
            <?php if (empty($data['unlocked'])): ?>
                <?php if ($i >= 6): ?>
                    <div class="ds-overlay"><img src="../img/gold.png" alt="Gold"></div>
                <?php else: ?>
                    <div class="ds-overlay">Level <?php echo htmlspecialchars($data['required_level']); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <img src="../img/sale.png" class="ds-sale" alt="unlocked">
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$pageCss = '../defaultslots/defaultslots.css';
$extraJs = '<script src="../defaultslots/defaultslots.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>