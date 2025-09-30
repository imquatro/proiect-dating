<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once '../includes/db.php';
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    exit('Access denied');
}

$id = $_POST['id'] ?? '';
if ($id === '') {
    header('Location: panel.php');
    exit;
}

$db->prepare('DELETE FROM user_achievements WHERE achievement_id = ?')->execute([$id]);
$db->prepare('DELETE FROM achievements WHERE id = ?')->execute([$id]);

header('Location: panel.php');
exit;