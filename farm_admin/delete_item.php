<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Accept both JSON payloads and form-encoded requests
$id = 0;

// Try standard POST first
if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
} else {
    // Fallback to JSON body
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
}

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

try {
    $stmt = $db->prepare('DELETE FROM farm_items WHERE id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Not found']);
        exit;
    }
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
