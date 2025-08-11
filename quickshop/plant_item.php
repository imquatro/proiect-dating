<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}
require_once '../includes/db.php';
$data = json_decode(file_get_contents('php://input'), true);
$slotId = intval($data['slot'] ?? 0);
$itemId = intval($data['item'] ?? 0);
$stmt = $db->prepare('SELECT image_plant FROM farm_items WHERE id = ?');
$stmt->execute([$itemId]);
$image = $stmt->fetchColumn();
if (!$image) {
    echo json_encode(['success' => false]);
    exit;
}
if (strpos($image, 'img/') !== 0) {
    $image = 'img/' . ltrim($image, '/');
}
// Planting logic would go here (e.g., update database)
// For now we just return the image path
echo json_encode(['success' => true, 'image' => '../' . $image]);