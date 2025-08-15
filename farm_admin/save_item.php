<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}

require_once __DIR__ . '/../includes/db.php';

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

    if($item_type === 'plant') {
        $feed_interval = 0;
        $feed_times = 0;
    } else {
        $water_interval = 0;
        $water_times = 0;
    }

    $imgPlant = fa_upload('image_plant');
    $imgProduct = fa_upload('image_product');
    $imgReady = $imgPlant;

    $stmt = $db->prepare('INSERT INTO farm_items (name,item_type,slot_type,image_plant,image_ready,image_product,water_interval,feed_interval,water_times,feed_times,price,production) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$name,$item_type,$slot_type,$imgPlant,$imgReady,$imgProduct,$water_interval,$feed_interval,$water_times,$feed_times,$price,$production]);

    header('Location: ../diverse.php');
    exit;
}

echo 'Invalid request';