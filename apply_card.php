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
$card = $_POST['card'] ?? '';
$card = trim($card);
if ($card === '') {
    $stmt = $db->prepare('UPDATE users SET vip_card = NULL WHERE id = ?');
    $stmt->execute([$user_id]);
    echo json_encode(['success'=>true]);
    exit;
}
if (!preg_match('/^[A-Za-z0-9._-]+$/', $card)) {
    echo json_encode(['success'=>false,'error'=>'Invalid card']);
    exit;
}
$path = __DIR__ . '/img/vip_cards/' . $card;
if (!is_file($path)) {
    echo json_encode(['success'=>false,'error'=>'Card not found']);
    exit;
}
$stmt = $db->prepare('UPDATE users SET vip_card = ? WHERE id = ?');
$stmt->execute([$card, $user_id]);

echo json_encode(['success'=>true]);