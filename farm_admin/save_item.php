<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

header('Content-Type: application/json');
require_once '../includes/db.php';

function fa_upload($field) {
    if (empty($_FILES[$field]['name'])) {
        return '';
    }
    $dir = __DIR__ . '/../img/farm_items/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $name = basename($_FILES[$field]['name']);
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $target = uniqid($field . '_') . '.' . $ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], $dir . $target);
    return 'img/farm_items/' . $target;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $item_type = $_POST['item_type'] ?? 'plant';
    $slot_type = $_POST['slot_type'] ?? 'crop';
    $water_interval = intval($_POST['water_hours'] ?? 0) * 3600 + intval($_POST['water_minutes'] ?? 0) * 60 + intval($_POST['water_seconds'] ?? 0);
    $feed_interval = intval($_POST['feed_hours'] ?? 0) * 3600 + intval($_POST['feed_minutes'] ?? 0) * 60 + intval($_POST['feed_seconds'] ?? 0);
    $water_times = intval($_POST['water_times'] ?? 0);
    $feed_times = intval($_POST['feed_times'] ?? 0);
    $price = intval($_POST['price'] ?? 0);
    $production = intval($_POST['production'] ?? 0);

    if ($item_type === 'plant') {
        $feed_interval = 0;
        $feed_times = 0;
    } else {
        $water_interval = 0;
        $water_times = 0;
    }

    $imgPlant = fa_upload('image_plant');
    $imgReady = fa_upload('image_ready');
    $imgProduct = fa_upload('image_product');

    try {
        $stmt = $db->prepare('INSERT INTO farm_items (name,item_type,slot_type,image_plant,image_ready,image_product,water_interval,feed_interval,water_times,feed_times,price,production,active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1)');
        $stmt->execute([$name,$item_type,$slot_type,$imgPlant,$imgReady,$imgProduct,$water_interval,$feed_interval,$water_times,$feed_times,$price,$production]);
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
            'production' => $production
        ]]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false]);