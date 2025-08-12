<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once '../includes/db.php';

$data   = json_decode(file_get_contents('php://input'), true);
$slotId = intval($data['slot'] ?? 0);
$itemId = intval($data['item'] ?? 0);

if (!$slotId || !$itemId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

// Verify item and price from database
$stmt = $db->prepare('SELECT price, image_plant FROM farm_items WHERE id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo json_encode(['success' => false]);
    exit;
}

$price = (int)$item['price'];
$image = $item['image_plant'];

if (strpos($image, 'img/') !== 0) {
    $image = 'img/' . ltrim($image, '/');
}

// Check user funds
$stmt = $db->prepare('SELECT money FROM users WHERE id = ?');
$stmt->execute([$userId]);
$money = (int)$stmt->fetchColumn();
if ($money < $price) {
    echo json_encode(['success' => false, 'error' => 'Insufficient funds']);
    exit;
}

// Deduct funds and store plant
try {
    $db->beginTransaction();
    $db->prepare('UPDATE users SET money = money - ? WHERE id = ?')
        ->execute([$price, $userId]);
    $db->prepare('INSERT INTO user_plants (user_id, slot_number, item_id, planted_at)
                  VALUES (?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE item_id = VALUES(item_id), planted_at = NOW()')
        ->execute([$userId, $slotId, $itemId]);
    $db->commit();
    echo json_encode(['success' => true, 'image' => $image]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}