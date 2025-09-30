<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $item_type = $_POST['item_type'] ?? 'plant';
    $slot_type = $_POST['slot_type'] ?? 'crop';
    $water_interval = intval($_POST['water_hours'] ?? 0) * 3600 + intval($_POST['water_minutes'] ?? 0) * 60 + intval($_POST['water_seconds'] ?? 0);
    $feed_interval = intval($_POST['feed_hours'] ?? 0) * 3600 + intval($_POST['feed_minutes'] ?? 0) * 60 + intval($_POST['feed_seconds'] ?? 0);
    $water_times = intval($_POST['water_times'] ?? 0);
    $feed_times = intval($_POST['feed_times'] ?? 0);
    $production = intval($_POST['production'] ?? 0);
    $price = intval($_POST['price'] ?? 0);
    $sell_price = intval($_POST['sell_price'] ?? 0);
    $barn_capacity = intval($_POST['barn_capacity'] ?? 0);
    $imgName = trim($_POST['image_name'] ?? '');
    $imgName = ltrim($imgName, '/');
    if (strpos($imgName, 'img/') === 0) {
        $imgName = substr($imgName, 4);
    }
    $imgFile = basename($imgName);
    if ($imgFile !== '') {
        $imgPlant = 'img/' . $imgFile;
        $imgReady = $imgPlant;
        $imgProduct = $imgPlant;
    } else {
        $stmtImg = $db->prepare('SELECT image_plant,image_ready,image_product FROM farm_items WHERE id=?');
        $stmtImg->execute([$id]);
        $existing = $stmtImg->fetch(PDO::FETCH_ASSOC);
        $imgPlant = $existing['image_plant'] ?? '';
        $imgReady = $existing['image_ready'] ?? '';
        $imgProduct = $existing['image_product'] ?? '';
    }
    if ($item_type === 'plant') {
        $feed_interval = 0;
        $feed_times = 0;
    } else {
        $water_interval = 0;
        $water_times = 0;
    }
    $stmt = $db->prepare('UPDATE farm_items SET name=?,item_type=?,slot_type=?,image_plant=?,image_ready=?,image_product=?,water_interval=?,feed_interval=?,water_times=?,feed_times=?,production=?,price=?,sell_price=?,barn_capacity=? WHERE id=?');
    $stmt->execute([$name,$item_type,$slot_type,$imgPlant,$imgReady,$imgProduct,$water_interval,$feed_interval,$water_times,$feed_times,$production,$price,$sell_price,$barn_capacity,$id]);
    echo json_encode(['success' => true, 'item' => [
        'id' => $id,
        'name' => $name,
        'item_type' => $item_type,
        'slot_type' => $slot_type,
        'image_plant' => $imgPlant,
        'water_interval' => $water_interval,
        'feed_interval' => $feed_interval,
        'water_times' => $water_times,
        'feed_times' => $feed_times,
        'production' => $production,
        'price' => $price,
        'sell_price' => $sell_price,
        'barn_capacity' => $barn_capacity
    ]]);
    exit;
}

echo json_encode(['success' => false]);
exit;