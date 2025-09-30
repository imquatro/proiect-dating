<?php
session_start(['read_and_close' => true]);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helper_images.php';
$helpers = $db->query('SELECT id,name,image,waters,feeds,harvests FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($helpers as &$h) {
    $h['image'] = resolve_helper_image($h['image']);
    if (strpos($h['image'], 'img/') !== 0) {
        $h['image'] = 'img/' . ltrim($h['image'], '/');
    }
}
unset($h);
echo json_encode(['helpers' => $helpers]);