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
require_once __DIR__ . '/includes/achievement_helpers.php';

$userId = (int)$_SESSION['user_id'];
$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;
$slot = isset($_POST['slot']) ? (int)$_POST['slot'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if ($itemId <= 0 || $slot <= 0 || $quantity <= 0) {
    echo json_encode(['error' => 'invalid_params']);
    exit;
}

$vipStmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$vipStmt->execute([$userId]);
$isVip = (int)$vipStmt->fetchColumn() > 0;

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

    $priceStmt = $db->prepare('SELECT sell_price, name FROM farm_items WHERE id = ?');
    $priceStmt->execute([$itemId]);
    $priceRow = $priceStmt->fetch(PDO::FETCH_ASSOC);
    $sellPrice = (int)$priceRow['sell_price'];
    $itemName = $priceRow['name'];

    $total = $sellPrice * $quantity;

    $loanStmt = $db->prepare('SELECT id, amount_due, amount_repaid FROM bank_loans WHERE user_id = ? AND repaid_time IS NULL ORDER BY start_time FOR UPDATE');
    $loanStmt->execute([$userId]);
    $loans = $loanStmt->fetchAll(PDO::FETCH_ASSOC);
    $outstanding = 0;
    foreach ($loans as $ln) {
        $outstanding += ($ln['amount_due'] - $ln['amount_repaid']);
    }
    $repayTotal = min((int)floor($total * 0.7), $outstanding);
    $userGain = $total - $repayTotal;

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

    $moneyUpd = $db->prepare('UPDATE users SET money = money + ?, sales = sales + ? WHERE id = ?');
    $moneyUpd->execute([$userGain, $quantity, $userId]);

    if ($repayTotal > 0 && $loans) {
        $remainingRepay = $repayTotal;
        foreach ($loans as $ln) {
            if ($remainingRepay <= 0) break;
            $loanRemaining = $ln['amount_due'] - $ln['amount_repaid'];
            if ($loanRemaining <= 0) continue;
            $apply = min($loanRemaining, $remainingRepay);
            $db->prepare('UPDATE bank_loans SET amount_repaid = amount_repaid + ?, repaid_time = IF(amount_repaid + ? >= amount_due, NOW(), repaid_time) WHERE id = ?')
                ->execute([$apply, $apply, $ln['id']]);
            $db->prepare('INSERT INTO bank_loan_payments (loan_id, item_id, item_name, quantity, sale_total, applied, created_at) VALUES (?,?,?,?,?,?,NOW())')
                ->execute([$ln['id'], $itemId, $itemName, $quantity, $total, $apply]);
            $remainingRepay -= $apply;
        }
    }

    // XP gain: VIP members earn 10 XP per full stack of 1000 sold
    $prodStmt = $db->prepare('SELECT production FROM farm_items WHERE id = ?');
    $prodStmt->execute([$itemId]);
    $production = (int)$prodStmt->fetchColumn();
    $xpGain = 0;
    if ($isVip && $production !== 1) {
        $xpGain = 10 * (int)floor($quantity / 1000);
    }
    $xpResult = $xpGain > 0 ? add_xp($db, $userId, $xpGain) : ['levelUp' => false, 'newLevel' => 0, 'xpGain' => 0];

    $walletStmt = $db->prepare('SELECT money, gold FROM users WHERE id = ?');
    $walletStmt->execute([$userId]);
    $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

    $db->commit();

    // Award achievements after successful sale
    check_and_award_achievements($db, $userId);

    echo json_encode([
        'success' => true,
        'remaining' => $remaining,
        'money' => isset($wallet['money']) ? (int)$wallet['money'] : 0,
        'gold' => isset($wallet['gold']) ? (int)$wallet['gold'] : 0,
        'levelUp' => $xpResult['levelUp'],
        'newLevel' => $xpResult['newLevel'],
        'xpGain' => $xpResult['xpGain'],
        'moneyGain' => $userGain
    ]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['error' => 'server_error']);
}
