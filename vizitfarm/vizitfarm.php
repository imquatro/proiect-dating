<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/slot_helpers.php';

$visitId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($visitId <= 0) {
    header('Location: ../friends.php');
    exit;
}

$stmt = $db->prepare('SELECT username, gallery, level FROM users WHERE id = ?');
$stmt->execute([$visitId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: ../friends.php');
    exit;
}

$avatar = 'default-avatar.png';
if (!empty($user['gallery'])) {
    $gal = array_filter(explode(',', $user['gallery']));
    if (!empty($gal)) {
        $candidatePath = __DIR__ . '/../uploads/' . $visitId . '/' . trim($gal[0]);
        if (is_file($candidatePath)) {
            $avatar = 'uploads/' . $visitId . '/' . trim($gal[0]);
        }
    }
}
$username = $user['username'];
$level = isset($user['level']) ? (int)$user['level'] : 1;

// Check if the visiting user is friends with the profile owner
$isFriend = false;
if (isset($_SESSION['user_id'])) {
    $currentId = (int)$_SESSION['user_id'];
    $friendStmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = "accepted"');
    $friendStmt->execute([$currentId, $visitId, $visitId, $currentId]);
    $isFriend = (bool)$friendStmt->fetchColumn();
}

$slotData = [];
$slotStmt = $db->prepare('SELECT ds.slot_number, COALESCE(us.unlocked, ds.unlocked) AS unlocked, COALESCE(us.required_level, ds.required_level) AS required_level FROM default_slots ds LEFT JOIN user_slots us ON us.user_id = ? AND us.slot_number = ds.slot_number');
$slotStmt->execute([$visitId]);
foreach ($slotStmt as $row) {
    $slotData[(int)$row['slot_number']] = $row;
}

ob_start();
?>
<div class="mini-profile">
    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
    <div class="mini-profile-card">
        <div class="username"><?= htmlspecialchars($username) ?></div>
        <div class="divider"></div>
        <div class="level">LVL: <?= htmlspecialchars($level) ?></div>
    </div>
</div>
<hr class="farm-divider">
<div class="farm-slots">
<?php
$total_slots = 35;
$slots_per_row = 5;
for ($i = 0; $i < $total_slots; $i++) {
    if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
    $slot_id = $i + 1;
    $data = $slotData[$slot_id] ?? ['unlocked' => 0, 'required_level' => 0];
    $isUnlocked = !empty($data['unlocked']);
    $classes = 'farm-slot' . ($isUnlocked ? '' : ' locked');
    $baseImg = get_slot_image($slot_id, $visitId);
    $imgFullPath = __DIR__ . '/../' . $baseImg;
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
            $required = get_slot_required_level($slot_id);
            echo '<div class="slot-overlay">Level ' . htmlspecialchars($required) . '</div>';
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
$pageCss = 'vizitfarm/vizitfarm.css';
$extraJs = '<script>window.isVisitor = true; window.visitId = ' . $visitId . '; window.canInteract = ' . ($isFriend ? 'true' : 'false') . ';</script>'
         . '<script src="vizitfarm/vizitfarm.js"></script>'
         . '<script src="assets_js/slot-items.js"></script>';
$activePage = '';
$baseHref = '../';
chdir('..');
include 'template.php';
?>