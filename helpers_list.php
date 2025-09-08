<?php
session_start(['read_and_close' => true]);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/includes/db.php';
$helpers = $db->query('SELECT id,name,image FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$selected = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT helper_id FROM user_helpers WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $selected = (int)$stmt->fetchColumn();
}
echo json_encode(['helpers' => $helpers, 'selected' => $selected]);