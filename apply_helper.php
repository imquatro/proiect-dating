<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once __DIR__ . '/includes/db.php';

$helperId = isset($_POST['helper_id']) ? (int)$_POST['helper_id'] : 0;
if (!$helperId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$today = date('Y-m-d');

$existing = $db->prepare('SELECT helper_id, last_action_date FROM user_helpers WHERE user_id = ?');
$existing->execute([$userId]);
$row = $existing->fetch(PDO::FETCH_ASSOC);
if ($row && $row['last_action_date'] === $today) {
    echo json_encode(['success' => false]);
    exit;
}

$check = $db->prepare('SELECT id FROM helpers WHERE id = ?');
$check->execute([$helperId]);
if (!$check->fetchColumn()) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $db->prepare('INSERT INTO user_helpers (user_id, helper_id, waters, feeds, harvests, last_action_date)
                      VALUES (?, ?, 0, 0, 0, ?)
                      ON DUPLICATE KEY UPDATE helper_id = VALUES(helper_id),
                                          waters = 0,
                                          feeds = 0,
                                          harvests = 0,
                                          last_action_date = VALUES(last_action_date)');
$stmt->execute([$userId, $helperId, $today]);

echo json_encode(['success' => true]);