<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
$mini_avatar = 'dating/default-avatar.png';
$user_name = 'Vizitator';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT username, gallery FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
        if (!empty($gallery)) {
            $candidate = 'dating/uploads/' . $user_id . '/' . $gallery[0];
            if (is_file($candidate)) {
                $mini_avatar = $candidate;
            }
        }
        $user_name = $user['username'] ?? $user_name;
    }
}
?>
<div class="mini-profile">
    <img src="<?= htmlspecialchars($mini_avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
    <div class="mini-profile-info">
        <div class="mini-profile-username"><?= htmlspecialchars($user_name) ?></div>
        <div class="mini-profile-stats">
            <span>Level: 1</span> | <span>XP: 0</span>
        </div>
    </div>
</div>