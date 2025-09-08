<?php
session_start(['read_and_close' => true]);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helper_images.php';
$helpers = $db->query('SELECT id,name,image FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($helpers as &$h) {
    $h['image'] = resolve_helper_image($h['image']);
}
unset($h);
foreach ($helpers as &$h) {
    if (strpos($h['image'], 'img/') !== 0) {
        $h['image'] = 'img/' . ltrim($h['image'], '/');
    }
}
$selected = 0;
$counts = ['waters' => 0, 'feeds' => 0, 'harvests' => 0];
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT helper_id, waters, feeds, harvests FROM user_helpers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = (int)$row['helper_id'];
        $counts = [
            'waters' => (int)$row['waters'],
            'feeds' => (int)$row['feeds'],
            'harvests' => (int)$row['harvests']
        ];
    }
}
echo json_encode(['helpers' => $helpers, 'selected' => $selected, 'counts' => $counts]);