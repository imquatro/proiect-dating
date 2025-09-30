<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $item_type = $_POST['item_type'] ?? 'plant';
    $slot_type = $_POST['slot_type'] ?? 'crop';
    $water_interval = intval($_POST['water_hours'] ?? 0) * 3600 + intval($_POST['water_minutes'] ?? 0) * 60 + intval($_POST['water_seconds'] ?? 0);
    $feed_interval = intval($_POST['feed_hours'] ?? 0) * 3600 + intval($_POST['feed_minutes'] ?? 0) * 60 + intval($_POST['feed_seconds'] ?? 0);
    $water_times = intval($_POST['water_times'] ?? 0);
    $feed_times = intval($_POST['feed_times'] ?? 0);
    $price = intval($_POST['price'] ?? 0);
    $sell_price = intval($_POST['sell_price'] ?? 0);
    $production = intval($_POST['production'] ?? 0);

    if ($item_type === 'plant') {
        $feed_interval = 0;
        $feed_times = 0;
    } else {
        $water_interval = 0;
        $water_times = 0;
    }

    $imgName = trim($_POST['image_name'] ?? '');
    $imgName = ltrim($imgName, '/');
    if (strpos($imgName, 'img/') === 0) {
        $imgName = substr($imgName, 4);
    }
    $imgFile = basename($imgName);
    $imgPlant = $imgFile !== '' ? 'img/' . $imgFile : '';
    $imgReady = $imgPlant;
    $imgProduct = $imgPlant;

    try {
        $stmt = $db->prepare('INSERT INTO farm_items (name,item_type,slot_type,image_plant,image_ready,image_product,water_interval,feed_interval,water_times,feed_times,price,sell_price,production,active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1)');
        $stmt->execute([$name,$item_type,$slot_type,$imgPlant,$imgReady,$imgProduct,$water_interval,$feed_interval,$water_times,$feed_times,$price,$sell_price,$production]);
        $id = $db->lastInsertId();
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
            'price' => $price,
            'sell_price' => $sell_price,
            'production' => $production
        ]]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false]);
