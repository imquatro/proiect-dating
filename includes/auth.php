<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$publicPages = [
    'index.php',
    'login.php',
    'register.php'
];

$current = basename($_SERVER['PHP_SELF']);
if (!in_array($current, $publicPages, true) && empty($_SESSION['user_id'])) {
    header('Location: index.php?login=1');
    exit;
}
?>