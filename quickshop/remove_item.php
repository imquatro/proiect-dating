<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$slotId = intval($data['slot'] ?? 0);
if (!$slotId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db->beginTransaction();
    $db->prepare('DELETE FROM user_plants WHERE user_id = ? AND slot_number = ?')
        ->execute([$userId, $slotId]);
    $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?')
        ->execute([$userId, $slotId]);
    $db->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}