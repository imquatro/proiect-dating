<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}
require_once '../includes/db.php';
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    echo json_encode(['success' => false]);
    exit;
}
 $id = (int)($_POST['id'] ?? 0);
 $name = trim($_POST['name'] ?? '');
 $image = trim($_POST['image'] ?? '');
 $image = preg_replace('/[^A-Za-z0-9_-]/', '', pathinfo($image, PATHINFO_FILENAME));
 $message = trim($_POST['message_file'] ?? '');
if ($id <= 0 || $name === '' || $image === '' || $message === '') {
    echo json_encode(['success' => false]);
    exit;
}
$stmt = $db->prepare('UPDATE helpers SET name = ?, image = ?, message_file = ? WHERE id = ?');
$stmt->execute([$name, $image, $message, $id]);
echo json_encode(['success' => true]);