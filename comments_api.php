<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [];
}
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    echo json_encode(array_values($_SESSION['comments']));
    exit;
}
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = trim($input['text'] ?? '');
    if ($text === '') {
        echo json_encode(['error' => 'empty']);
        exit;
    }
    $id = uniqid();
    $comment = ['id' => $id, 'user' => 'Tu', 'text' => $text];
    $_SESSION['comments'][$id] = $comment;
    echo json_encode($comment);
    exit;
}
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (isset($_SESSION['comments'][$id])) {
        unset($_SESSION['comments'][$id]);
    }
    echo json_encode(['ok' => true]);
    exit;
}
http_response_code(405);
?>
