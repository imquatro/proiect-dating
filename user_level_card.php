<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
$username = 'Vizitator';
$level = 1;
$isVip = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare('SELECT username, level, vip FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $username = $user['username'];
        $level = (int)$user['level'];
        $isVip = !empty($user['vip']);
    }
}
?>
<div class="user-level-card">
    <div class="username<?= $isVip ? ' gold-shimmer' : '' ?>"><?= htmlspecialchars($username) ?></div>
    <div class="divider"></div>
    <div class="level">LVL: <?= htmlspecialchars($level) ?></div>
</div>