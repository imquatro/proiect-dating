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

$baseMoneyCost = 10000000; // 10 million
$baseGoldCost  = 50;
$defaultCapacity = 4;

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

    $capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ? FOR UPDATE');
    $capStmt->execute([$userId]);
    $capacity = (int)$capStmt->fetchColumn();
    if (!$capacity) {
        $capacity = $defaultCapacity;
        $ins = $db->prepare('INSERT INTO user_barn_info (user_id, capacity) VALUES (?, ?)');
        $ins->execute([$userId, $capacity]);
    }

    $slotsPurchased = max(0, $capacity - $defaultCapacity);
    $moneyCost = $baseMoneyCost * (1 << $slotsPurchased);
    $goldCost  = $baseGoldCost  * (1 << $slotsPurchased);

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

    $db->prepare('UPDATE user_barn_info SET capacity = capacity + 1 WHERE user_id = ?')->execute([$userId]);

    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

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