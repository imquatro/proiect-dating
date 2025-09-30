<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}
require_once __DIR__ . '/includes/db.php';

$db->exec("CREATE TABLE IF NOT EXISTS bank_deposits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    interest INT NOT NULL,
    hours INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    claimed TINYINT(1) NOT NULL DEFAULT 0,
    INDEX(user_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS bank_loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount INT NOT NULL,
    amount_due INT NOT NULL,
    amount_repaid INT NOT NULL DEFAULT 0,
    start_time DATETIME NOT NULL,
    repaid_time DATETIME DEFAULT NULL,
    INDEX(user_id)
)");

$db->exec("CREATE TABLE IF NOT EXISTS bank_loan_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    sale_total INT NOT NULL,
    applied INT NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX(loan_id)
)");

$userId = (int)$_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

// Determine VIP status for deposit limits
$stmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$stmt->execute([$userId]);
$isVip = (int)$stmt->fetchColumn() > 0;
$vipMax = 5;
$baseMax = 2;
$maxDeposits = $isVip ? $vipMax : $baseMax;

function getMoney($db, $uid) {
    $stmt = $db->prepare('SELECT money FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    return (int)$stmt->fetchColumn();
}

if ($action === 'loan') {
    $amount = (int)($_POST['amount'] ?? 0);
    $amount = max(1000, min(10000, $amount));
    $amount = (int)(floor($amount / 1000) * 1000);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_loans WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    if ($count >= 3) {
        echo json_encode(['error' => 'Daily loan limit reached', 'remaining' => 0]);
        exit;
    }
    $start = date('Y-m-d H:i:s');
    $due = $amount * 2;
    $db->beginTransaction();
    $db->prepare('INSERT INTO bank_loans (user_id, amount, amount_due, start_time) VALUES (?,?,?,?)')
        ->execute([$userId, $amount, $due, $start]);
    $db->prepare('UPDATE users SET money = money + ? WHERE id = ?')->execute([$amount, $userId]);
    $db->commit();
    $remaining = max(0, 3 - ($count + 1));
    $money = getMoney($db, $userId);
    echo json_encode([
        'success' => true,
        'money' => $money,
        'loan' => ['amount' => $amount, 'amount_due' => $due, 'start_time' => $start],
        'remaining' => $remaining
    ]);
    exit;
}

if ($action === 'loan_active') {
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_loans WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $loanRemaining = max(0, 3 - (int)$stmt->fetchColumn());
    $stmt = $db->prepare('SELECT id, amount, amount_due, amount_repaid, start_time FROM bank_loans WHERE user_id = ? AND repaid_time IS NULL ORDER BY start_time');
    $stmt->execute([$userId]);
    $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($loans as &$loan) {
        $pstmt = $db->prepare('SELECT item_name, quantity, applied, created_at FROM bank_loan_payments WHERE loan_id = ? ORDER BY id');
        $pstmt->execute([$loan['id']]);
        $loan['payments'] = $pstmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $money = getMoney($db, $userId);
    echo json_encode(['loans' => $loans, 'money' => $money, 'remaining' => $loanRemaining]);
    exit;
}

if ($action === 'deposit') {
    $hours = max(1, min(24, (int)($_REQUEST['hours'] ?? 1)));
    $amount = 1000000;
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    $remaining = max(0, $maxDeposits - $count);
    if ($money < $amount) {
        echo json_encode([
            'error' => 'Not enough funds',
            'money' => $money,
            'remaining' => $remaining,
            'max' => $maxDeposits,
            'vip' => $isVip,
            'count' => $count,
            'vip_max' => $vipMax,
            'base_max' => $baseMax
        ]);
        exit;
    }
    if ($count >= $maxDeposits) {
        echo json_encode([
            'error' => 'Daily deposit limit reached',
            'money' => $money,
            'remaining' => 0,
            'max' => $maxDeposits,
            'vip' => $isVip,
            'count' => $count,
            'vip_max' => $vipMax,
            'base_max' => $baseMax
        ]);
        exit;
    }
    $actualHours = $hours + 1;
    $interest = $hours * 1000;
    $start = date('Y-m-d H:i:s');
    $end = date('Y-m-d H:i:s', time() + $actualHours * 3600);
    $db->beginTransaction();
    $db->prepare('UPDATE users SET money = money - ? WHERE id = ?')->execute([$amount, $userId]);
    $db->prepare('INSERT INTO bank_deposits (user_id, amount, interest, hours, start_time, end_time) VALUES (?,?,?,?,?,?)')
        ->execute([$userId, $amount, $interest, $hours, $start, $end]);
    $db->commit();
    $money -= $amount;
    $count++;
    $remaining = max(0, $maxDeposits - $count);
    echo json_encode([
        'success' => true,
        'money' => $money,
        'deposit' => ['amount' => $amount, 'interest' => $interest, 'hours' => $hours, 'start_time' => $start, 'end_time' => $end],
        'remaining' => $remaining,
        'max' => $maxDeposits,
        'vip' => $isVip,
        'count' => $count,
        'vip_max' => $vipMax,
        'base_max' => $baseMax
    ]);
    exit;
}

if ($action === 'cancel') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $db->prepare('SELECT amount, end_time FROM bank_deposits WHERE id = ? AND user_id = ? AND claimed = 0');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['error' => 'Invalid deposit']);
        exit;
    }
    if (time() >= strtotime($row['end_time'])) {
        echo json_encode(['error' => 'Deposit already matured']);
        exit;
    }
    $amount = (int)$row['amount'];
    $db->beginTransaction();
    $db->prepare('DELETE FROM bank_deposits WHERE id = ?')->execute([$id]);
    $db->prepare('UPDATE users SET money = money + ? WHERE id = ?')->execute([$amount, $userId]);
    $db->commit();
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    $remaining = max(0, $maxDeposits - $count);
    echo json_encode([
        'success' => true,
        'money' => $money,
        'remaining' => $remaining,
        'max' => $maxDeposits,
        'vip' => $isVip,
        'count' => $count,
        'vip_max' => $vipMax,
        'base_max' => $baseMax
    ]);
    exit;
}

if ($action === 'claim') {
    $id = (int)($_POST['id'] ?? 0);
    $force = !empty($_POST['force']);
    $stmt = $db->prepare('SELECT amount, interest, end_time FROM bank_deposits WHERE id = ? AND user_id = ? AND claimed = 0');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['error' => 'Invalid deposit']);
        exit;
    }
    if (!$force && time() < strtotime($row['end_time'])) {
        echo json_encode(['error' => 'Deposit not matured yet']);
        exit;
    }
    $sum = (int)$row['amount'] + (int)$row['interest'];
    $db->beginTransaction();
    $db->prepare('UPDATE bank_deposits SET claimed = 1, end_time = NOW() WHERE id = ?')->execute([$id]);
    $db->prepare('UPDATE users SET money = money + ? WHERE id = ?')->execute([$sum, $userId]);
    $db->commit();
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    $remaining = max(0, $maxDeposits - $count);
    echo json_encode([
        'success' => true,
        'money' => $money,
        'remaining' => $remaining,
        'max' => $maxDeposits,
        'vip' => $isVip,
        'count' => $count,
        'vip_max' => $vipMax,
        'base_max' => $baseMax
    ]);
    exit;
}

if ($action === 'active') {
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    $remaining = max(0, $maxDeposits - $count);
    $stmt = $db->prepare('SELECT id, amount, interest, hours, start_time, end_time FROM bank_deposits WHERE user_id = ? AND claimed = 0 ORDER BY end_time');
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $r['display_end'] = $r['end_time'];
    }
    echo json_encode([
        'deposits' => $rows,
        'money' => $money,
        'remaining' => $remaining,
        'max' => $maxDeposits,
        'vip' => $isVip,
        'count' => $count,
        'vip_max' => $vipMax,
        'base_max' => $baseMax
    ]);
    exit;
}

if ($action === 'history') {
    $money = getMoney($db, $userId);
    $stmt = $db->prepare('SELECT amount, interest, hours, start_time, end_time FROM bank_deposits WHERE user_id = ? AND claimed = 1 ORDER BY end_time DESC LIMIT 50');
    $stmt->execute([$userId]);
    $depositHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($depositHistory as &$d) {
        $d['display_end'] = $d['end_time'];
    }
    $stmt = $db->prepare('SELECT id, amount, amount_due, start_time, repaid_time FROM bank_loans WHERE user_id = ? AND repaid_time IS NOT NULL ORDER BY repaid_time DESC LIMIT 50');
    $stmt->execute([$userId]);
    $loanHistory = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $pc = $db->prepare('SELECT COUNT(*) FROM bank_loan_payments WHERE loan_id = ?');
        $pc->execute([$row['id']]);
        $row['payments'] = (int)$pc->fetchColumn();
        $loanHistory[] = $row;
    }
    echo json_encode(['history' => $depositHistory, 'loan_history' => $loanHistory, 'money' => $money]);
    exit;
}

echo json_encode(['error' => 'Invalid action']);
