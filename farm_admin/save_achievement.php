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
$years = $_POST['years'] ?? 0;
$imageName = $_POST['image_name'] ?? '';

if (!$id || !$title || !$imageName) { echo json_encode(['success'=>false]); exit; }

$imgDir = __DIR__ . '/../img/achievements';
$image = '';
foreach (['png','gif','jpg','jpeg'] as $ext) {
    $candidate = "$imgDir/$imageName.$ext";
    if (is_file($candidate)) {
        $image = 'img/achievements/' . $imageName . '.' . $ext;
        break;
    }
}
if (!$image) { $image = 'img/achievements/default.png'; }

$stmt = $db->prepare('INSERT INTO achievements (id, title, harvest, sales, level, xp, item_id, years, image) VALUES (?,?,?,?,?,?,?,?,?)');
$ok = $stmt->execute([$id, $title, $harvest, $sales, $level, $xp, $item_id, $years, $image]);
echo json_encode(['success'=>$ok]);