<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require_once __DIR__ . '/includes/db.php';
$userId = (int)$_SESSION['user_id'];

// Ensure extended columns exist in user_slots
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = [];
    }
    $stmt = $db->prepare(
        'INSERT INTO user_slots (user_id, slot_number, item_id, plant_date, water_interval, feed_interval, water_remaining, feed_remaining, timer_type, timer_end)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            item_id = VALUES(item_id),
            plant_date = VALUES(plant_date),
            water_interval = VALUES(water_interval),
            feed_interval = VALUES(feed_interval),
            water_remaining = VALUES(water_remaining),
            feed_remaining = VALUES(feed_remaining),
            timer_type = VALUES(timer_type),
            timer_end = VALUES(timer_end)'
    );
    foreach ($input as $slotNumber => $data) {
        $itemId = isset($data['itemId']) ? (int)$data['itemId'] : null;
        $plantDate = isset($data['plantDate']) ? date('Y-m-d H:i:s', $data['plantDate']/1000) : null;
        $waterInterval = isset($data['waterInterval']) ? (int)$data['waterInterval'] : 0;
        $feedInterval = isset($data['feedInterval']) ? (int)$data['feedInterval'] : 0;
        $waterRemaining = isset($data['waterRemaining']) ? (int)$data['waterRemaining'] : 0;
        $feedRemaining = isset($data['feedRemaining']) ? (int)$data['feedRemaining'] : 0;
        $timerType = isset($data['timerType']) ? substr($data['timerType'], 0, 10) : null;
        $timerEnd = isset($data['timerEnd']) ? date('Y-m-d H:i:s', $data['timerEnd']/1000) : null;
        $stmt->execute([
            $userId,
            (int)$slotNumber,
            $itemId,
            $plantDate,
            $waterInterval,
            $feedInterval,
            $waterRemaining,
            $feedRemaining,
            $timerType,
            $timerEnd
        ]);
    }
    echo json_encode(['status' => 'ok']);
    exit;
}

$stmt = $db->prepare('SELECT us.slot_number, us.item_id, us.plant_date, us.water_interval, us.feed_interval, us.water_remaining, us.feed_remaining, us.timer_type, us.timer_end, fi.image_plant FROM user_slots us LEFT JOIN farm_items fi ON fi.id = us.item_id WHERE us.user_id = ?');
$stmt->execute([$userId]);
$states = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $slot = (int)$row['slot_number'];
    $img = $row['image_plant'];
    if ($img && strpos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
    }
    $states[$slot] = [
        'itemId' => $row['item_id'] !== null ? (int)$row['item_id'] : null,
        'plantDate' => $row['plant_date'] ? (strtotime($row['plant_date']) * 1000) : null,
        'image' => $img,
        'waterInterval' => (int)$row['water_interval'],
        'feedInterval' => (int)$row['feed_interval'],
        'waterRemaining' => (int)$row['water_remaining'],
        'feedRemaining' => (int)$row['feed_remaining'],
        'timerType' => $row['timer_type'],
        'timerEnd' => $row['timer_end'] ? (strtotime($row['timer_end']) * 1000) : null
    ];
}
echo json_encode($states);