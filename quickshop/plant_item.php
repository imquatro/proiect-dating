<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once '../includes/db.php';

// Ensure extended columns exist on user_slots
$columns = [
    'item_id INT DEFAULT NULL',
    'plant_date DATETIME DEFAULT NULL',
    'water_interval INT NOT NULL DEFAULT 0',
    'feed_interval INT NOT NULL DEFAULT 0',
    'water_remaining INT NOT NULL DEFAULT 0',
    'feed_remaining INT NOT NULL DEFAULT 0',
    'timer_type VARCHAR(10) DEFAULT NULL',
    'timer_end DATETIME DEFAULT NULL'
];
foreach ($columns as $def) {
    $db->exec("ALTER TABLE user_slots ADD COLUMN IF NOT EXISTS $def");
}

$data   = json_decode(file_get_contents('php://input'), true);
$slotId = intval($data['slot'] ?? 0);
$itemId = intval($data['item'] ?? 0);

if (!$slotId || !$itemId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

// Verify item and price from database
$stmt = $db->prepare('SELECT price, image_plant, water_interval, feed_interval FROM farm_items WHERE id = ?');
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo json_encode(['success' => false]);
    exit;
}

$price = (int)$item['price'];
$image = $item['image_plant'];
$waterInterval = (int)$item['water_interval'];
$feedInterval = (int)$item['feed_interval'];

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
    $db->prepare(
        'INSERT INTO user_slots (user_id, slot_number, item_id, plant_date, water_interval, feed_interval, water_remaining, feed_remaining, timer_type, timer_end)
         VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, NULL, NULL)
         ON DUPLICATE KEY UPDATE
            item_id = VALUES(item_id),
            plant_date = VALUES(plant_date),
            water_interval = VALUES(water_interval),
            feed_interval = VALUES(feed_interval),
            water_remaining = VALUES(water_remaining),
            feed_remaining = VALUES(feed_remaining),
            timer_type = NULL,
            timer_end = NULL'
    )->execute([
        $userId,
        $slotId,
        $itemId,
        $waterInterval,
        $feedInterval,
        $waterInterval,
        $feedInterval
    ]);
    $db->commit();
    echo json_encode(['success' => true, 'image' => $image]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}
