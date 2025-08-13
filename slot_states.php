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
$targetId = isset($_REQUEST['user_id']) ? (int)$_REQUEST['user_id'] : $userId;

// Ensure table exists
$db->exec('CREATE TABLE IF NOT EXISTS user_slot_states (
    user_id INT NOT NULL,
    slot_number INT NOT NULL,
    image VARCHAR(255) NOT NULL DEFAULT "",
    water_interval INT NOT NULL DEFAULT 0,
    feed_interval INT NOT NULL DEFAULT 0,
    water_remaining INT NOT NULL DEFAULT 0,
    feed_remaining INT NOT NULL DEFAULT 0,
    timer_type VARCHAR(10) DEFAULT NULL,
    timer_end DATETIME DEFAULT NULL,
    PRIMARY KEY (user_id, slot_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($targetId !== $userId) {
        $friendStmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = "accepted"');
        $friendStmt->execute([$userId, $targetId, $targetId, $userId]);
        if (!$friendStmt->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['error' => 'not_friends']);
            exit;
        }
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        $input = [];
    }

    $slotIds = array_map('intval', array_keys($input));
    if ($slotIds) {
        $placeholders = implode(',', array_fill(0, count($slotIds), '?'));
        $params = array_merge([$targetId], $slotIds);
        $db->prepare("DELETE FROM user_slot_states WHERE user_id = ? AND slot_number NOT IN ($placeholders)")
           ->execute($params);
    } else {
        $db->prepare('DELETE FROM user_slot_states WHERE user_id = ?')->execute([$targetId]);
    }

    $stmt = $db->prepare('INSERT INTO user_slot_states (user_id, slot_number, image, water_interval, feed_interval, water_remaining, feed_remaining, timer_type, timer_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE image = VALUES(image), water_interval = VALUES(water_interval), feed_interval = VALUES(feed_interval), water_remaining = VALUES(water_remaining), feed_remaining = VALUES(feed_remaining), timer_type = VALUES(timer_type), timer_end = VALUES(timer_end)');
    foreach ($input as $slotId => $state) {
        $image = isset($state['image']) ? $state['image'] : '';
        $waterInterval = isset($state['waterInterval']) ? (int)$state['waterInterval'] : 0;
        $feedInterval = isset($state['feedInterval']) ? (int)$state['feedInterval'] : 0;
        $waterRemaining = isset($state['waterRemaining']) ? (int)$state['waterRemaining'] : 0;
        $feedRemaining = isset($state['feedRemaining']) ? (int)$state['feedRemaining'] : 0;
        $timerType = isset($state['timerType']) ? substr($state['timerType'], 0, 10) : null;
        $timerEnd = isset($state['timerEnd']) ? date('Y-m-d H:i:s', $state['timerEnd']/1000) : null;
        $stmt->execute([$targetId, (int)$slotId, $image, $waterInterval, $feedInterval, $waterRemaining, $feedRemaining, $timerType, $timerEnd]);
    }

    echo json_encode(['status' => 'ok']);
    exit;
}

if ($targetId === $userId) {
    $db->prepare('UPDATE user_slot_states SET image = "", water_interval = 0, feed_interval = 0, water_remaining = 0, feed_remaining = 0, timer_type = NULL, timer_end = NULL WHERE user_id = ? AND slot_number NOT IN (SELECT slot_number FROM user_plants WHERE user_id = ?)')->execute([$targetId, $targetId]);
}

$stmt = $db->prepare('SELECT us.slot_number, us.image, us.water_interval, us.feed_interval, us.water_remaining, us.feed_remaining, us.timer_type, us.timer_end FROM user_slot_states us JOIN user_plants up ON up.user_id = us.user_id AND up.slot_number = us.slot_number WHERE us.user_id = ?');
$stmt->execute([$targetId]);
$states = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $slot = (int)$row['slot_number'];
    $timerEnd = $row['timer_end'] ? (strtotime($row['timer_end']) * 1000) : null;
    $states[$slot] = [
        'image' => $row['image'],
        'waterInterval' => (int)$row['water_interval'],
        'feedInterval' => (int)$row['feed_interval'],
        'waterRemaining' => (int)$row['water_remaining'],
        'feedRemaining' => (int)$row['feed_remaining'],
        'timerType' => $row['timer_type'],
        'timerEnd' => $timerEnd
    ];
}
echo json_encode($states);
