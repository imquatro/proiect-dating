<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/includes/db.php';

if (empty($_SESSION['user_id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $_SESSION['user_id'];
$achId = (int)$_POST['id'];

$stmt = $db->prepare('UPDATE user_achievements SET selected = 0 WHERE user_id = ? AND achievement_id = ?');
$stmt->execute([$userId, $achId]);

echo json_encode(['success' => true, 'image' => 'img/achievements/default.png']);