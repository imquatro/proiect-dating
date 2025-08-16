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
$stmt = $db->prepare("
    SELECT ds.slot_number,
           COALESCE(us.unlocked, ds.unlocked) AS unlocked,
           COALESCE(us.required_level, ds.required_level) AS required_level
    FROM default_slots ds
    LEFT JOIN user_slots us
        ON us.user_id = ? AND us.slot_number = ds.slot_number
    ORDER BY ds.slot_number
");
$stmt->execute([$userId]);
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
$slotData = [];
foreach ($slots as $slot) {
    $slotData[(int)$slot['slot_number']] = $slot;
}
$lvlStmt = $db->prepare('SELECT level FROM users WHERE id = ?');
$lvlStmt->execute([$userId]);
$userLevel = (int)$lvlStmt->fetchColumn();
$total_slots = 35;
$bgImagePath = '../img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../img/bg2.png');
$ajax = isset($_GET['ajax']);
ob_start();
?>
    <div id="ds-slot-menu">
        <?php for ($i = 1; $i <= $total_slots; $i++):
            $data = $slotData[$i] ?? ['unlocked' => 0, 'required_level' => 0];
            $required = get_slot_required_level($i);
            if ($data['required_level'] != $required) {
                $db->prepare('UPDATE user_slots SET required_level = ? WHERE user_id = ? AND slot_number = ?')
                   ->execute([$required, $userId, $i]);
                $data['required_level'] = $required;
            }
            $isUnlocked = !empty($data['unlocked']);
            if (!$isUnlocked && $required > 0 && $userLevel >= $required && $i <= $total_slots - 5) {
                $db->prepare('UPDATE user_slots SET unlocked = 1 WHERE user_id = ? AND slot_number = ?')
                   ->execute([$userId, $i]);
                $isUnlocked = true;
            }
            $classes = 'ds-slot';
            if ($i === 1) { $classes .= ' active'; }
            $classes .= $isUnlocked ? ' open' : ' locked';
        ?>
        <?php $baseImg = get_slot_image($i, $userId); ?>
        <div class="<?php echo $classes; ?>" data-slot="<?php echo $i; ?>">
            <img src="../<?php echo $baseImg; ?>" class="slot-img" alt="Slot <?php echo $i; ?>">
            <?php if (!$isUnlocked): ?>
                <?php if ($i > $total_slots - 5): ?>
                    <div class="ds-overlay"><img src="../img/gold.png" alt="Gold"></div>
                <?php else: ?>
                    <div class="ds-overlay">Level <?php echo htmlspecialchars($required); ?></div>
                <?php endif; ?>
            <?php else: ?>
                <img src="../img/sale.png" class="ds-sale" alt="unlocked">
            <?php endif; ?>
        </div>
        <?php endfor; ?>
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
