<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $other_id = (int)$_POST['user_id'];
    $stmt = $db->prepare("DELETE FROM messages WHERE (sender_id = :u AND receiver_id = :o) OR (sender_id = :o AND receiver_id = :u)");
    $stmt->execute(['u' => $user_id, 'o' => $other_id]);
    echo json_encode(['success' => true]);
    exit;
}
http_response_code(400);
?>