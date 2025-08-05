<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
$user_id = $_SESSION['user_id'];

$stmt = $db->prepare('SELECT sender_id, COUNT(*) AS cnt FROM messages WHERE receiver_id = ? AND is_read = 0 GROUP BY sender_id');
$stmt->execute([$user_id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = 0;
foreach ($details as $d) { $total += (int)$d['cnt']; }
header('Content-Type: application/json');
echo json_encode(['total' => $total, 'details' => $details]);