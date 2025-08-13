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
$ownerId = isset($_POST['owner_id']) ? (int)$_POST['owner_id'] : 0;
$slotId = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
if (!$ownerId || !$action || !$slotId) {
    echo json_encode(['status' => 'error']);
    exit;
}

if ($ownerId === $userId) {
    echo json_encode(['status' => 'ok']);
    exit;
}

$friendStmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = "accepted"');
$friendStmt->execute([$userId, $ownerId, $ownerId, $userId]);
if (!$friendStmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['error' => 'not_friends']);
    exit;
}

$db->exec('CREATE TABLE IF NOT EXISTS user_last_helpers (
    owner_id INT PRIMARY KEY,
    helper_id INT NOT NULL,
    action ENUM("water","feed") NOT NULL,
    helped_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

$stmt = $db->prepare('INSERT INTO user_last_helpers (owner_id, helper_id, action, helped_at) VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE helper_id = VALUES(helper_id), action = VALUES(action), helped_at = VALUES(helped_at)');
$stmt->execute([$ownerId, $userId, $action]);

$db->exec('CREATE TABLE IF NOT EXISTS slot_helpers (
    owner_id INT NOT NULL,
    slot_number INT NOT NULL,
    helper_id INT NOT NULL,
    water_clicks INT NOT NULL DEFAULT 0,
    feed_clicks INT NOT NULL DEFAULT 0,
    last_action_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (owner_id, slot_number, helper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

if ($action === 'water') {
    $hstmt = $db->prepare('INSERT INTO slot_helpers (owner_id, slot_number, helper_id, water_clicks, feed_clicks, last_action_at) VALUES (?, ?, ?, 1, 0, NOW())
        ON DUPLICATE KEY UPDATE water_clicks = water_clicks + 1, last_action_at = NOW()');
} else {
    $hstmt = $db->prepare('INSERT INTO slot_helpers (owner_id, slot_number, helper_id, water_clicks, feed_clicks, last_action_at) VALUES (?, ?, ?, 0, 1, NOW())
        ON DUPLICATE KEY UPDATE feed_clicks = feed_clicks + 1, last_action_at = NOW()');
}
$hstmt->execute([$ownerId, $slotId, $userId]);

echo json_encode(['status' => 'ok']);
