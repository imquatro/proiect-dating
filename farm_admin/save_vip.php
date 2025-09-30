<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}
$type = $_POST['vip_type'] ?? '';
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
$dir = __DIR__ . '/../img/vip_' . ($type === 'frame' ? 'frames' : 'cards');
$target = $dir . '/' . $name;
if (is_file($target)) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Image not found']);
}