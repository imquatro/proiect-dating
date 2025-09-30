<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}
require_once __DIR__ . '/includes/db.php';
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user || empty($user['vip'])) {
    echo json_encode(['success'=>false,'error'=>'VIP required']);
    exit;
}
$frame = $_POST['frame'] ?? '';
$frame = trim($frame);
if ($frame === '') {
    $stmt = $db->prepare('UPDATE users SET vip_frame = NULL WHERE id = ?');
    $stmt->execute([$user_id]);
    echo json_encode(['success'=>true]);
    exit;
}
if (!preg_match('/^[A-Za-z0-9._-]+$/', $frame)) {
    echo json_encode(['success'=>false,'error'=>'Invalid frame']);
    exit;
}
$path = __DIR__ . '/img/vip_frames/' . $frame;
if (!is_file($path)) {
    echo json_encode(['success'=>false,'error'=>'Frame not found']);
    exit;
}
$stmt = $db->prepare('UPDATE users SET vip_frame = ? WHERE id = ?');
$stmt->execute([$frame, $user_id]);

echo json_encode(['success'=>true]);