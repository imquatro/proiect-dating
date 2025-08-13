<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
require_once __DIR__ . '/includes/db.php';
$userId = (int)$_SESSION['user_id'];
$stmt = $db->prepare('SELECT ulh.helper_id, ulh.action, ulh.helped_at, u.gallery FROM user_last_helpers ulh JOIN users u ON u.id = ulh.helper_id WHERE ulh.owner_id = ?');
$stmt->execute([$userId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $gallery = !empty($row['gallery']) ? array_filter(explode(',', $row['gallery'])) : [];
    $photo = 'default-avatar.png';
    if (!empty($gallery)) {
        $candidate = 'uploads/' . $row['helper_id'] . '/' . $gallery[0];
        if (is_file($candidate)) {
            $photo = $candidate;
        }
    }
    echo json_encode([
        'helper_id' => (int)$row['helper_id'],
        'action' => $row['action'],
        'helped_at' => $row['helped_at'],
        'photo' => $photo
    ]);
} else {
    echo json_encode([]);
}