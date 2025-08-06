<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
?>
<div class="wallet-display">
    <div class="currency money"><img src="img/money.png" alt="Money"><span id="moneyAmount"><?= htmlspecialchars($money) ?></span></div>
    <div class="currency gold"><img src="img/gold.png" alt="Gold"><span id="goldAmount"><?= htmlspecialchars($gold) ?></span></div>
</div>