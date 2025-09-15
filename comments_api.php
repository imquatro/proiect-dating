<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$targetId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['user_id'];
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}
if (!isset($_SESSION['comments'][$targetId])) {
    $_SESSION['comments'][$targetId] = [];
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    echo json_encode(array_values($_SESSION['comments'][$targetId]));
    exit;
}
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['text'] ?? '');
    if ($text === '') {
        echo json_encode(['error' => 'empty']);
        exit;
    }
    $currentId = (int)$_SESSION['user_id'];
    if ($targetId !== $currentId) {
        $stmt = $db->prepare('SELECT 1 FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = \'accepted\'');
        $stmt->execute([$currentId, $targetId, $targetId, $currentId]);
        if (!$stmt->fetchColumn()) {
            http_response_code(403);
            echo json_encode(['error' => 'not_friends']);
            exit;
        }
    }
    $stmt = $db->prepare('SELECT username FROM users WHERE id = ?');
    $stmt->execute([$currentId]);
    $username = $stmt->fetchColumn() ?: 'User';
    $id = uniqid();
    $comment = ['id' => $id, 'user' => $username, 'text' => $text];
    $_SESSION['comments'][$targetId][$id] = $comment;
    echo json_encode($comment);
    exit;
}
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    unset($_SESSION['comments'][$targetId][$id]);
    echo json_encode(['ok' => true]);
    exit;
}
http_response_code(405);
?>
