<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}
$stmt = $db->prepare('SELECT id,name,item_type,slot_type,image_plant,image_product,water_interval,feed_interval,water_times,feed_times,production,price,sell_price,barn_capacity FROM farm_items WHERE id=?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}
echo json_encode($item);
exit;
