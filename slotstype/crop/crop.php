<?php
session_start();
require_once __DIR__ . '/../../includes/db.php';

$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
$apply = isset($_GET['apply']);

if ($apply && $slotId && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT money FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $money = (int)$stmt->fetchColumn();

    if ($money >= 1000) {
        $db->prepare('UPDATE users SET money = money - 1000 WHERE id = ?')->execute([$userId]);
        $source = __DIR__ . '/../../img/default.png';
        $dest = __DIR__ . "/../../img/slot{$slotId}.png";
        if (file_exists($source)) {
            copy($source, $dest);
        }
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'error' => 'Insufficient funds'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$bgImagePath = '../../img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
?>
<div id="crop-slot" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <h2>Crop Slot Placeholder</h2>
</div>