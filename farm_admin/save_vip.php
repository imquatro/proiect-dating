<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}
$type = $_POST['vip_type'] ?? '';
$name = $_POST['image_name'] ?? '';
if (!in_array($type, ['frame','card'], true)) {
    echo json_encode(['success'=>false,'error'=>'Invalid type']);
    exit;
}
if (!preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
    echo json_encode(['success'=>false,'error'=>'Invalid name']);
    exit;
}
if (empty($_FILES['vip_image']['tmp_name'])) {
    echo json_encode(['success'=>false,'error'=>'No image']);
    exit;
}
$dir = __DIR__ . '/../img/vip_' . ($type === 'frame' ? 'frames' : 'cards');
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}
$target = $dir . '/' . $name;
if (move_uploaded_file($_FILES['vip_image']['tmp_name'], $target)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Upload failed']);
}