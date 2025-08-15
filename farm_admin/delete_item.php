<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}
require_once '../includes/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}
$stmt = $db->prepare('DELETE FROM farm_items WHERE id = ?');
$ok = $stmt->execute([$id]);
echo json_encode(['success' => $ok]);
?>
