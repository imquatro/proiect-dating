<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$userId = (int)$_SESSION['user_id'];
$type = $_POST['type'] ?? '';
$moneyCost = 10000000; // 10 million
$goldCost = 50;

if ($type !== 'money' && $type !== 'gold') {
    echo json_encode(['success' => false, 'error' => 'invalid_type']);
    exit;
}

try {
    $db->beginTransaction();

    $walletStmt = $db->prepare('SELECT money, gold FROM users WHERE id = ? FOR UPDATE');
    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);
    if (!$wallet) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'wallet_not_found']);
        exit;
    }

    if ($type === 'money') {
        if ((int)$wallet['money'] < $moneyCost) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'not_enough_money']);
            exit;
        }
        $upd = $db->prepare('UPDATE users SET money = money - ? WHERE id = ?');
        $upd->execute([$moneyCost, $userId]);
    } else {
        if ((int)$wallet['gold'] < $goldCost) {
            $db->rollBack();
            echo json_encode(['success' => false, 'error' => 'not_enough_gold']);
            exit;
        }
        $upd = $db->prepare('UPDATE users SET gold = gold - ? WHERE id = ?');
        $upd->execute([$goldCost, $userId]);
    }

    $capStmt = $db->prepare('INSERT INTO user_barn_info (user_id, capacity) VALUES (?, 17)
                              ON DUPLICATE KEY UPDATE capacity = capacity + 1');
    $capStmt->execute([$userId]);

    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

    $capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ?');
    $capStmt->execute([$userId]);
    $capacity = (int)$capStmt->fetchColumn();

    $db->commit();

    echo json_encode([
        'success' => true,
        'capacity' => $capacity,
        'money' => isset($wallet['money']) ? (int)$wallet['money'] : 0,
        'gold' => isset($wallet['gold']) ? (int)$wallet['gold'] : 0
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'server_error']);
}
?>