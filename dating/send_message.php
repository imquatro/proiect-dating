<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'], $_POST['message'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_id = (int)$_POST['user_id'];
$message = trim($_POST['message']);

if ($message === '' || $other_id === $user_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $db->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
$stmt->execute([$user_id, $other_id, $message]);
$insertId = $db->lastInsertId();

$stmt = $db->prepare('SELECT gallery FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$gallery = $stmt->fetchColumn();
$avatar = 'default-avatar.png';
if (!empty($gallery)) {
    $gal = array_filter(explode(',', $gallery));
    if ($gal) {
        $avatar = 'uploads/' . $user_id . '/' . trim($gal[0]);
    }
}

$stmt = $db->prepare('SELECT created_at FROM messages WHERE id = ?');
$stmt->execute([$insertId]);
$created_at = $stmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => [
        'id' => $insertId,
        'sender_id' => $user_id,
        'message' => $message,
        'created_at' => $created_at,
        'avatar' => $avatar
    ]
]);