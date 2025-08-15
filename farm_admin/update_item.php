<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once __DIR__ . '/../includes/db.php';

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
    if ($item_type === 'plant') {
        $feed_interval = 0;
        $feed_times = 0;
    } else {
        $water_interval = 0;
        $water_times = 0;
    }
    $imgName = trim($_POST['image_name'] ?? '');
    $imgPlant = $imgName ? 'img/' . ltrim($imgName, '/') : '';
    $imgProduct = $imgPlant;
    $imgReady = $imgPlant;
    $stmt = $db->prepare('UPDATE farm_items SET name=?,item_type=?,slot_type=?,image_plant=?,image_ready=?,image_product=?,water_interval=?,feed_interval=?,water_times=?,feed_times=?,production=?,price=?,sell_price=?,barn_capacity=? WHERE id=?');
    $stmt->execute([$name,$item_type,$slot_type,$imgPlant,$imgReady,$imgProduct,$water_interval,$feed_interval,$water_times,$feed_times,$production,$price,$sell_price,$barn_capacity,$id]);
    header('Location: ../diverse.php');
    exit;
}
echo 'Invalid request';
