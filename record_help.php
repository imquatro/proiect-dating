<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/level_helpers.php';
$userId = (int)$_SESSION['user_id'];
$ownerId = isset($_POST['owner_id']) ? (int)$_POST['owner_id'] : 0;
$slotId = isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

$vipStmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$vipStmt->execute([$userId]);
$isVip = (int)$vipStmt->fetchColumn() > 0;
$xpPerAction = $isVip ? 4 : 1;
if (!$ownerId || !$action || !$slotId) {
    echo json_encode(['status' => 'error']);
    exit;
}

if ($ownerId === $userId) {
    $result = add_xp($db, $userId, $xpPerAction);
    echo json_encode(array_merge(['status' => 'ok'], $result));
    exit;
}

$friendStmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = \'accepted\'');
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
    helped_at DATETIME NOT NULL,
    clicks INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

@$db->exec('ALTER TABLE user_last_helpers ADD COLUMN IF NOT EXISTS clicks INT NOT NULL DEFAULT 1');

$stmt = $db->prepare(
    'INSERT INTO user_last_helpers (owner_id, helper_id, action, helped_at, clicks) VALUES (?, ?, ?, NOW(), 1)
        ON DUPLICATE KEY UPDATE
            clicks = IF(
                helper_id = VALUES(helper_id)
                AND action = VALUES(action)
                AND TIMESTAMPDIFF(SECOND, helped_at, NOW()) <= 5,
                clicks + 1,
                1
            ),
            helper_id = VALUES(helper_id),
            action = VALUES(action),
            helped_at = NOW()'
);
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

// Actually perform the action on the server
$actionSuccess = false;
try {
    if ($action === 'water') {
        $checkStmt = $db->prepare('SELECT water_remaining FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
        $checkStmt->execute([$ownerId, $slotId]);
        $waterRemaining = (int)$checkStmt->fetchColumn();
        
        if ($waterRemaining > 0) {
            $newWaterRemaining = $waterRemaining - 1;
            $intervalStmt = $db->prepare('SELECT water_interval FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
            $intervalStmt->execute([$ownerId, $slotId]);
            $waterInterval = (int)$intervalStmt->fetchColumn();
            
            if ($newWaterRemaining > 0 && $waterInterval > 0) {
                $timerEnd = date('Y-m-d H:i:s', time() + $waterInterval);
                $updStmt = $db->prepare('UPDATE user_slot_states SET water_remaining = ?, timer_type = \'water\', timer_end = ?, updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
                $updStmt->execute([$newWaterRemaining, $timerEnd, $ownerId, $slotId]);
            } else {
                $feedCheck = $db->prepare('SELECT feed_remaining FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
                $feedCheck->execute([$ownerId, $slotId]);
                $feedRemaining = (int)$feedCheck->fetchColumn();
                
                if ($feedRemaining > 0) {
                    $updStmt = $db->prepare('UPDATE user_slot_states SET water_remaining = 0, timer_type = NULL, timer_end = NULL, updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
                } else {
                    $updStmt = $db->prepare('UPDATE user_slot_states SET water_remaining = 0, timer_type = \'harvest\', timer_end = NULL, updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
                }
                $updStmt->execute([$ownerId, $slotId]);
            }
            $actionSuccess = true;
        }
    } elseif ($action === 'feed') {
        $checkStmt = $db->prepare('SELECT feed_remaining FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
        $checkStmt->execute([$ownerId, $slotId]);
        $feedRemaining = (int)$checkStmt->fetchColumn();
        
        if ($feedRemaining > 0) {
            $newFeedRemaining = $feedRemaining - 1;
            $intervalStmt = $db->prepare('SELECT feed_interval FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
            $intervalStmt->execute([$ownerId, $slotId]);
            $feedInterval = (int)$intervalStmt->fetchColumn();
            
            if ($newFeedRemaining > 0 && $feedInterval > 0) {
                $timerEnd = date('Y-m-d H:i:s', time() + $feedInterval);
                $updStmt = $db->prepare('UPDATE user_slot_states SET feed_remaining = ?, timer_type = \'feed\', timer_end = ?, updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
                $updStmt->execute([$newFeedRemaining, $timerEnd, $ownerId, $slotId]);
            } else {
                $updStmt = $db->prepare('UPDATE user_slot_states SET feed_remaining = 0, timer_type = \'harvest\', timer_end = NULL, updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
                $updStmt->execute([$ownerId, $slotId]);
            }
            $actionSuccess = true;
        }
    }
} catch (Exception $e) {
    // Action failed, but still award XP for the attempt
}

$result = add_xp($db, $userId, $xpPerAction);

// Return complete slot state for instant UI update
$slotState = null;
if ($actionSuccess) {
    try {
        $stateStmt = $db->prepare('SELECT water_remaining, feed_remaining, timer_type, timer_end FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
        $stateStmt->execute([$ownerId, $slotId]);
        $stateRow = $stateStmt->fetch(PDO::FETCH_ASSOC);
        if ($stateRow) {
            $slotState = [
                'slotId' => $slotId,
                'waterRemaining' => (int)$stateRow['water_remaining'],
                'feedRemaining' => (int)$stateRow['feed_remaining'],
                'timerType' => $stateRow['timer_type'],
                'timerEnd' => $stateRow['timer_end'] ? strtotime($stateRow['timer_end']) * 1000 : null
            ];
        }
    } catch (Exception $e) {
        // Ignore error
    }
}

echo json_encode(array_merge(['status' => 'ok', 'actionPerformed' => $actionSuccess, 'slotState' => $slotState], $result));
