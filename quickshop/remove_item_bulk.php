<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

require_once '../includes/db.php';
require_once '../includes/slot_helpers.php';

$data = json_decode(file_get_contents('php://input'), true);
$slotId = intval($data['slot'] ?? 0);
if (!$slotId) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT item_id FROM user_plants WHERE user_id = ? AND slot_number = ?');
    $stmt->execute([$userId, $slotId]);
    $itemId = $stmt->fetchColumn();
    if (!$itemId) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }

    $slotsStmt = $db->prepare('SELECT slot_number FROM user_plants WHERE user_id = ? AND item_id = ?');
    $slotsStmt->execute([$userId, $itemId]);
    $slots = $slotsStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$slots) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }

    $delPlant = $db->prepare('DELETE FROM user_plants WHERE user_id = ? AND slot_number = ?');
    $delState = $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?');

    $slotImages = [];
    foreach ($slots as $s) {
        $delPlant->execute([$userId, $s]);
        $delState->execute([$userId, $s]);
        $base = get_slot_image($s, $userId);
        $basePath = dirname(__DIR__) . '/' . $base;
        $slotImages[] = [
            'slot' => (int)$s,
            'image' => $base . '?v=' . (file_exists($basePath) ? filemtime($basePath) : time())
        ];
    }

    $db->commit();
    echo json_encode(['success' => true, 'slots' => $slotImages]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}