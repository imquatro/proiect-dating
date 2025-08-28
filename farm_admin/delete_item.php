<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false]);
    exit;
}

try {
    $stmt = $db->prepare('DELETE FROM farm_items WHERE id = ?');
    $stmt->execute([$id]);
    echo json_encode(['success' => $stmt->rowCount() > 0]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
?>