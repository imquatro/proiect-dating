<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$slotId = isset($data['slot']) ? (int)$data['slot'] : 0;
if (!$slotId) {
    echo json_encode(['success' => false]);
    exit;
}
$userId = (int)$_SESSION['user_id'];

$db->exec('CREATE TABLE IF NOT EXISTS user_barn (
    user_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

try {
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT item_id FROM user_plants WHERE user_id = ? AND slot_number = ?');
    $stmt->execute([$userId, $slotId]);
    $itemId = $stmt->fetchColumn();
    if (!$itemId) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $stmt = $db->prepare('SELECT production, image_product FROM farm_items WHERE id = ?');
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $qty = (int)$item['production'];
    $img = $item['image_product'];
    if (strpos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
    }
    $db->prepare('DELETE FROM user_plants WHERE user_id = ? AND slot_number = ?')->execute([$userId, $slotId]);
    $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?')->execute([$userId, $slotId]);
    $db->prepare('INSERT INTO user_barn (user_id, item_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)')->execute([$userId, $itemId, $qty]);
    $db->commit();
    echo json_encode(['success' => true, 'item' => ['item_id' => (int)$itemId, 'quantity' => $qty, 'image' => $img]]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}