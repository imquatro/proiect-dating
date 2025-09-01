<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/level_helpers.php';

$userId = (int)$_SESSION['user_id'];
$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($itemId <= 0 || $slot <= 0 || $quantity <= 0) {
    echo json_encode(['error' => 'invalid_params']);
    exit;
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare('SELECT quantity FROM user_barn WHERE user_id = ? AND slot_number = ? AND item_id = ? FOR UPDATE');
    $stmt->execute([$userId, $slot, $itemId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $db->rollBack();
        echo json_encode(['error' => 'not_found']);
        exit;
    }
    $currentQty = (int)$row['quantity'];
    if ($quantity > $currentQty) {
        $db->rollBack();
        echo json_encode(['error' => 'insufficient_quantity']);
        exit;
    }

    $priceStmt = $db->prepare('SELECT sell_price FROM farm_items WHERE id = ?');
    $priceStmt->execute([$itemId]);
    $sellPrice = (int)$priceStmt->fetchColumn();

    $total = $sellPrice * $quantity;

    if ($currentQty - $quantity > 0) {
        $upd = $db->prepare('UPDATE user_barn SET quantity = ? WHERE user_id = ? AND slot_number = ?');
        $upd->execute([$currentQty - $quantity, $userId, $slot]);
        $remaining = $currentQty - $quantity;
    } else {
        $del = $db->prepare('DELETE FROM user_barn WHERE user_id = ? AND slot_number = ?');
        $del->execute([$userId, $slot]);
        $shift = $db->prepare('UPDATE user_barn SET slot_number = slot_number - 1 WHERE user_id = ? AND slot_number > ? ORDER BY slot_number');
        $shift->execute([$userId, $slot]);
        $remaining = 0;
    }

    $moneyUpd = $db->prepare('UPDATE users SET money = money + ? WHERE id = ?');
    $moneyUpd->execute([$total, $userId]);

    // XP gain: 50 XP per stack sold (stack size 1000 or 1)
    $prodStmt = $db->prepare('SELECT production FROM farm_items WHERE id = ?');
    $prodStmt->execute([$itemId]);
    $production = (int)$prodStmt->fetchColumn();
    $stackSize = ($production === 1) ? 1 : 1000;
    $xpGain = 50 * (int)ceil($quantity / $stackSize);
    $xpResult = add_xp($db, $userId, $xpGain);

    $walletStmt = $db->prepare('SELECT money, gold FROM users WHERE id = ?');
    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

    $db->commit();

    echo json_encode([
        'success' => true,
        'remaining' => $remaining,
        'money' => isset($wallet['money']) ? (int)$wallet['money'] : 0,
        'gold' => isset($wallet['gold']) ? (int)$wallet['gold'] : 0,
        'levelUp' => $xpResult['levelUp'],
        'newLevel' => $xpResult['newLevel'],
        'xpGain' => $xpResult['xpGain'],
        'moneyGain' => $total
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['error' => 'server_error']);
}
