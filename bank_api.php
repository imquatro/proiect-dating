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

$userId = (int)$_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';

function getMoney($db, $uid) {
    $stmt = $db->prepare('SELECT money FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    return (int)$stmt->fetchColumn();
}

if ($action === 'deposit') {
    $hours = max(1, min(24, (int)($_REQUEST['hours'] ?? 1)));
    $amount = 1000000;
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $count = (int)$stmt->fetchColumn();
    $remaining = max(0, 10 - $count);
    if ($money < $amount) {
        echo json_encode(['error' => 'Not enough funds', 'money' => $money, 'remaining' => $remaining]);
        exit;
    }
    if ($count >= 10) {
        echo json_encode(['error' => 'Daily deposit limit reached', 'money' => $money, 'remaining' => 0]);
        exit;
    }
    $interest = $hours * 100;
    $start = date('Y-m-d H:i:s');
    $end = date('Y-m-d H:i:s', time() + $hours * 3600);
    $db->beginTransaction();
    $db->prepare('UPDATE users SET money = money - ? WHERE id = ?')->execute([$amount, $userId]);
    $db->prepare('INSERT INTO bank_deposits (user_id, amount, interest, hours, start_time, end_time) VALUES (?,?,?,?,?,?)')
        ->execute([$userId, $amount, $interest, $hours, $start, $end]);
    $db->commit();
    $money -= $amount;
    $remaining = max(0, 10 - ($count + 1));
    echo json_encode(['success' => true, 'money' => $money, 'deposit' => ['amount' => $amount, 'interest' => $interest, 'hours' => $hours, 'start_time' => $start, 'end_time' => $end], 'remaining' => $remaining]);
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
    $remaining = max(0, 10 - (int)$stmt->fetchColumn());
    echo json_encode(['success' => true, 'money' => $money, 'remaining' => $remaining]);
    exit;
}

if ($action === 'active' || $action === 'history') {
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare('SELECT id, amount, interest FROM bank_deposits WHERE user_id = ? AND claimed = 0 AND end_time <= ?');
    $stmt->execute([$userId, $now]);
    $matured = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($matured) {
        $sum = 0;
        foreach ($matured as $m) {
            $sum += $m['amount'] + $m['interest'];
            $db->prepare('UPDATE bank_deposits SET claimed = 1 WHERE id = ?')->execute([$m['id']]);
        }
        if ($sum > 0) {
            $db->prepare('UPDATE users SET money = money + ? WHERE id = ?')->execute([$sum, $userId]);
        }
    }
    $money = getMoney($db, $userId);
    $today = date('Y-m-d 00:00:00');
    $stmt = $db->prepare('SELECT COUNT(*) FROM bank_deposits WHERE user_id = ? AND start_time >= ?');
    $stmt->execute([$userId, $today]);
    $remaining = max(0, 10 - (int)$stmt->fetchColumn());
    if ($action === 'active') {
        $stmt = $db->prepare('SELECT id, amount, interest, hours, start_time, end_time FROM bank_deposits WHERE user_id = ? AND claimed = 0 ORDER BY end_time');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['deposits' => $rows, 'money' => $money, 'remaining' => $remaining]);
        exit;
    } else {
        $stmt = $db->prepare('SELECT amount, interest, hours, start_time, end_time FROM bank_deposits WHERE user_id = ? AND claimed = 1 ORDER BY end_time DESC LIMIT 50');
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['history' => $rows, 'money' => $money, 'remaining' => $remaining]);
        exit;
    }
}

echo json_encode(['error' => 'Invalid action']);