<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/includes/helper_actions.php';

$userId = (int)$_SESSION['user_id'];
$force = isset($_GET['force']) && $_GET['force'] === '1';
$summary = process_helper_actions($userId, $force);

header('Content-Type: application/json');
echo json_encode($summary);
