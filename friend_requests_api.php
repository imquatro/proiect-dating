<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}
require_once __DIR__ . '/includes/db.php';
$user_id = (int)$_SESSION['user_id'];
try {
    $stmt = $db->prepare('SELECT COUNT(*) FROM friend_requests WHERE receiver_id = ? AND status = \'pending\'');
    $stmt->execute([$user_id]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
