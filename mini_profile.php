<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
$mini_avatar = 'default-avatar.png';
$user_name = 'Vizitator';
$user_level = 1;
$isVip = false;
$frame = 'img/frames/defaultframe.png';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT username, gallery, level, vip, vip_frame FROM users WHERE id = ?');
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
        $isVip = !empty($user['vip']);
        if (!empty($user['vip_frame'])) {
            $candidate = 'img/vip_frames/' . $user['vip_frame'];
            if (is_file($candidate)) {
                $frame = $candidate;
            }
        }
    }
}
?>
<div class="mini-cards-row">
    <div class="mini-card helpers-card" id="helpersCard"></div>
    <div class="mini-profile" id="miniProfile">
        <div id="helper-effect" class="helper-effect mini-card">
            <img src="" alt="Helper">
            <div class="combo-count"></div>
        </div>
        <div class="avatar-wrapper">
            <img src="<?= htmlspecialchars($mini_avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
        </div>
        <div class="mini-profile-card">
            <div class="level-circle"><?= htmlspecialchars($user_level) ?></div>
            <div class="username<?= $isVip ? ' gold-shimmer' : '' ?>"><?= htmlspecialchars($user_name) ?></div>
            <div class="divider"></div>
        </div>
        <img src="<?= htmlspecialchars($frame) ?>" alt="Frame" class="mini-profile-frame" />
    </div>
    <div class="mini-card achievements-card" id="achievementsCard"></div>
</div>
<script src="assets_js/mini-profile.js"></script>
