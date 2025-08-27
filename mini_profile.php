<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
$mini_avatar = 'default-avatar.png';
$user_name = 'Vizitator';
$user_level = 1;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT username, gallery, level FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
        if (!empty($gallery)) {
            $candidate = 'uploads/' . $user_id . '/' . $gallery[0];
            if (is_file($candidate)) {
                $mini_avatar = $candidate;
            }
        }
        $user_name = $user['username'] ?? $user_name;
        $user_level = isset($user['level']) ? (int)$user['level'] : $user_level;
    }
}
?>
<div class="mini-profile-wrapper">
    <div class="mini-card helpers-card" id="helpersCard"></div>
    <div class="mini-profile" id="miniProfile">
        <div id="helper-effect" class="helper-effect">
            <img src="" alt="Helper">
        </div>
        <img src="<?= htmlspecialchars($mini_avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
        <div class="mini-profile-card">
            <div class="username"><?= htmlspecialchars($user_name) ?></div>
            <div class="divider"></div>
            <div class="level">LVL: <?= htmlspecialchars($user_level) ?></div>
        </div>
    </div>
    <div class="mini-card achievements-card" id="achievementsCard"></div>
</div>