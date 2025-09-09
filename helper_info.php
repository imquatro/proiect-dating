<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/includes/helper_actions.php';

$userId = (int)$_SESSION['user_id'];
$summary = process_helper_actions($userId);

header('Content-Type: application/json');
echo json_encode($summary);
