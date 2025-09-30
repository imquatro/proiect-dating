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
 $waters = max(0, (int)($_POST['waters'] ?? 0));
 $feeds = max(0, (int)($_POST['feeds'] ?? 0));
 $harvests = max(0, (int)($_POST['harvests'] ?? 0));
if ($id <= 0 || $name === '' || $image === '' || $message === '') {
    echo json_encode(['success' => false]);
    exit;
}
$stmt = $db->prepare('UPDATE helpers SET name = ?, image = ?, message_file = ?, waters = ?, feeds = ?, harvests = ? WHERE id = ?');
$stmt->execute([$name, $image, $message, $waters, $feeds, $harvests, $id]);
echo json_encode(['success' => true]);