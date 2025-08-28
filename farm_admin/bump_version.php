<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}
$version = time();
file_put_contents(__DIR__ . '/../version.txt', $version);
header('Content-Type: application/json');
echo json_encode(['success' => true, 'version' => $version]);
