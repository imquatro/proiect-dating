<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    exit;
}

$visitId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($visitId <= 0) {
    exit;
}

$_GET['user_id'] = $visitId;

require __DIR__ . '/../profile_comments_panel.php';