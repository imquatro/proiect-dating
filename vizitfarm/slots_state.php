<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/slot_helpers.php';

$visitId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($visitId <= 0) {
    echo json_encode(['error' => 'missing_id']);
    exit;
}

$stmt = $db->prepare('SELECT level FROM users WHERE id = ?');
$stmt->execute([$visitId]);
$level = (int)$stmt->fetchColumn();
if (!$level) {
    echo json_encode(['error' => 'user_not_found']);
    exit;
}

$total_slots = 35;
$slotStmt = $db->prepare('SELECT ds.slot_number, COALESCE(us.unlocked, ds.unlocked) AS unlocked, COALESCE(us.required_level, ds.required_level) AS required_level FROM default_slots ds LEFT JOIN user_slots us ON us.user_id = ? AND us.slot_number = ds.slot_number');
$slotStmt->execute([$visitId]);
$slots = [];
while ($row = $slotStmt->fetch(PDO::FETCH_ASSOC)) {
    $slot_id = (int)$row['slot_number'];
    $required = get_slot_required_level($slot_id);
    $isUnlocked = !empty($row['unlocked']);
    if (!$isUnlocked && $required > 0 && $level >= $required && $slot_id <= $total_slots - 5) {
        $isUnlocked = true;
    }
    $imgPath = get_slot_image($slot_id, $visitId);
    $imgFullPath = __DIR__ . '/../' . $imgPath;
    $imgSrc = $imgPath . '?v=' . (file_exists($imgFullPath) ? filemtime($imgFullPath) : time());
    $slots[] = [
        'id' => $slot_id,
        'image' => $imgSrc,
        'unlocked' => $isUnlocked,
        'required' => $required,
        'premium' => ($slot_id > $total_slots - 5)
    ];
}

echo json_encode(['slots' => $slots]);