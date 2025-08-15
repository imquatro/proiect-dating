<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once __DIR__ . '/../includes/db.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare('SELECT id,name,item_type,slot_type,image_plant,image_ready,image_product,water_interval,feed_interval,water_times,feed_times,production,price,barn_capacity FROM farm_items WHERE id=?');
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo json_encode(['error' => 'Not found']);
    exit;
}
echo json_encode($item);
