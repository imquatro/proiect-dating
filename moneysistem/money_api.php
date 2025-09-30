<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$money = 0;
$gold = 0;

if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../includes/db.php';
    $stmt = $db->prepare('SELECT money, gold FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($wallet) {
        $money = (int)$wallet['money'];
        $gold = (int)$wallet['gold'];
    }
}

echo json_encode(['money' => $money, 'gold' => $gold]);