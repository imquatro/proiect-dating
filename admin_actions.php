<?php
session_start();
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nu ești autentificat']);
    exit;
}

// Verifică dacă userul curent e admin
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$currentUser || !$currentUser['is_admin']) {
    echo json_encode(['success' => false, 'message' => 'Nu ai drepturi admin']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$user_id = $data['user_id'] ?? 0;

if (!$user_id || !in_array($action, ['set_admin', 'remove_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Date invalide']);
    exit;
}

if ($action === 'set_admin') {
    $stmt = $db->prepare("UPDATE users SET is_admin = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'message' => 'Utilizator setat ca admin cu succes']);
    exit;
}

if ($action === 'remove_admin') {
    $stmt = $db->prepare("UPDATE users SET is_admin = 0 WHERE id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true, 'message' => 'Admin eliminat cu succes']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acțiune necunoscută']);
