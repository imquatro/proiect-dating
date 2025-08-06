<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    http_response_code(400);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_id = (int)$_GET['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $db->prepare('SELECT m.*, u.gallery FROM messages m JOIN users u ON m.sender_id = u.id WHERE ((m.sender_id = :uid AND m.receiver_id = :oid) OR (m.sender_id = :oid AND m.receiver_id = :uid)) AND m.id > :last_id ORDER BY m.id ASC');
$stmt->execute(['uid' => $user_id, 'oid' => $other_id, 'last_id' => $last_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$messages = [];
foreach ($rows as $row) {
    $avatar = 'default-avatar.png';
    if (!empty($row['gallery'])) {
        $gal = explode(',', $row['gallery']);
        $avatar = 'uploads/' . $row['sender_id'] . '/' . trim($gal[0]);
    }
    $messages[] = [
        'id'         => $row['id'],
        'sender_id'  => $row['sender_id'],
        'message'    => $row['message'],
        'created_at' => $row['created_at'],
        'avatar'     => $avatar
    ];
}
if (!empty($messages)) {
    $stmt = $db->prepare('UPDATE messages SET is_read = 1 WHERE sender_id = :oid AND receiver_id = :uid AND is_read = 0');
    $stmt->execute(['oid' => $other_id, 'uid' => $user_id]);
}
header('Content-Type: application/json');
echo json_encode(['messages' => $messages]);
