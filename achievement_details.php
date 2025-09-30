<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/includes/db.php';

// Validate input and determine target user
$achId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (empty($_SESSION['user_id']) || $achId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

// When visiting another profile use the provided user id, otherwise use current user
$userId = isset($_GET['user']) ? (int)$_GET['user'] : (int)$_SESSION['user_id'];

// Fetch achievement details
$stmt = $db->prepare('SELECT * FROM achievements WHERE id = ?');
$stmt->execute([$achId]);
$ach = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ach) {
    echo json_encode(['success' => false]);
    exit;
}

// Fetch user data for progress calculation
$uStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$uStmt->execute([$userId]);
$user = $uStmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['success' => false]);
    exit;
}

$progress = 0;
$detail = '';
$needed = 0;
$current = 0;

if (!empty($ach['level'])) {
    $needed = (int)$ach['level'];
    $current = (int)($user['level'] ?? 0);
    $progress = $needed > 0 ? min(100, $current / $needed * 100) : 0;
    $detail = 'Necesită nivel ' . $needed;
} elseif (!empty($ach['xp'])) {
    $needed = (int)$ach['xp'];
    $current = (int)($user['xp'] ?? 0);
    $progress = $needed > 0 ? min(100, $current / $needed * 100) : 0;
    $detail = 'Necesită ' . $needed . ' XP';
} elseif (!empty($ach['years'])) {
    $needed = (int)$ach['years'];
    $created = new DateTime($user['created_at'] ?? 'now');
    $now = new DateTime();
    $diffYears = $created->diff($now)->days / 365;
    $current = $diffYears;
    $progress = $needed > 0 ? min(100, $diffYears / $needed * 100) : 0;
    $detail = 'Necesită ' . $needed . ' ani';
} elseif (!empty($ach['harvest'])) {
    $needed = (int)$ach['harvest'];
    $current = (int)($user['harvest'] ?? $user['harvests'] ?? $user['harvest_count'] ?? 0);
    $progress = $needed > 0 ? min(100, $current / $needed * 100) : 0;
    $detail = 'Necesită ' . $needed . ' recolte';
} elseif (!empty($ach['sales'])) {
    $needed = (int)$ach['sales'];
    $current = (int)($user['sales'] ?? $user['sales_count'] ?? 0);
    $progress = $needed > 0 ? min(100, $current / $needed * 100) : 0;
    $detail = 'Necesită ' . $needed . ' vânzări';
}

echo json_encode([
    'success' => true,
    'title' => $ach['title'],
    'image' => $ach['image'],
    'progress' => round($progress, 2),
    'detail' => $detail
]);