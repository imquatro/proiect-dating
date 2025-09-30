<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$db->exec('CREATE TABLE IF NOT EXISTS user_barn (
    user_id INT NOT NULL,
    slot_number INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    PRIMARY KEY (user_id, slot_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

$db->exec('CREATE TABLE IF NOT EXISTS user_barn_info (
    user_id INT NOT NULL PRIMARY KEY,
    capacity INT NOT NULL DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

$userId = (int)$_SESSION['user_id'];

$capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ?');
$capStmt->execute([$userId]);
$capacity = (int)$capStmt->fetchColumn();
if (!$capacity) {
    $capacity = 4;
    $db->prepare('INSERT INTO user_barn_info (user_id, capacity) VALUES (?, ?)')->execute([$userId, $capacity]);
}

$stmt = $db->prepare('SELECT ub.slot_number, ub.item_id, ub.quantity, fi.image_product, fi.sell_price, fi.name FROM user_barn ub JOIN farm_items fi ON fi.id = ub.item_id WHERE ub.user_id = ? ORDER BY ub.slot_number');
$stmt->execute([$userId]);
$items = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $img = $row['image_product'];
    if (strpos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
    }
    $items[] = [
        'slot' => (int)$row['slot_number'],
        'item_id' => (int)$row['item_id'],
        'quantity' => (int)$row['quantity'],
        'image' => $img,
        'sell_price' => (int)$row['sell_price'],
        'name' => $row['name']
    ];
}
echo json_encode(['capacity' => $capacity, 'items' => $items]);