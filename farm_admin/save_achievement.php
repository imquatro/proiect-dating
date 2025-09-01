<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit; }
require_once __DIR__ . '/../includes/db.php';
$id = $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$harvest = $_POST['harvest'] ?? 0;
$sales = $_POST['sales'] ?? 0;
$level = $_POST['level'] ?? 0;
$xp = $_POST['xp'] ?? 0;
$item_id = $_POST['item_id'] !== '' ? $_POST['item_id'] : null;
$image = $_POST['image'] ?? '';
if (!$id || !$title || !$image) { echo json_encode(['success'=>false]); exit; }
$stmt = $db->prepare('INSERT INTO achievements (id, title, harvest, sales, level, xp, item_id, image) VALUES (?,?,?,?,?,?,?,?)');
$ok = $stmt->execute([$id, $title, $harvest, $sales, $level, $xp, $item_id, $image]);
echo json_encode(['success'=>$ok]);