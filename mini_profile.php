<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/achievement_helpers.php';

$mini_defaults = [
    'show_helpers' => true,
    'show_profile' => true,
    'show_achievements' => true,
    'show_helper_effect' => true,
    'center_single' => false,
];
$mini_profile_config = array_merge($mini_defaults, isset($mini_profile_config) && is_array($mini_profile_config) ? $mini_profile_config : []);

$mini_avatar = 'default-avatar.png';
$user_name = 'Vizitator';
$user_level = 1;
$isVip = false;
$frame = 'img/frames/defaultframe.png';
$cardBg = 'img/bg2.png';
$selectedAchievements = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $db->prepare('SELECT username, gallery, level, vip, vip_frame, vip_card, created_at FROM users WHERE id = ?');
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
        if (!empty($user['vip_card'])) {
            $candidate = 'img/vip_cards/' . $user['vip_card'];
            if (is_file($candidate)) {
                $cardBg = $candidate;
            }
        }

        // Check for newly earned achievements
        check_and_award_achievements($db, $user_id);

        $achStmt = $db->prepare('SELECT a.image FROM achievements a JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = ? AND ua.selected = 1');
        $achStmt->execute([$user_id]);
        $selectedAchievements = $achStmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

if (empty($selectedAchievements)) {
    $selectedAchievements[] = 'img/achievements/default.png';
}
?>
<div class="mini-cards-row<?= $mini_profile_config['center_single'] ? ' single-achievement' : '' ?>">
    <?php if ($mini_profile_config['show_helpers']): ?>
    <div class="mini-card helpers-card" id="helpersCard"></div>
    <?php endif; ?>

    <?php if ($mini_profile_config['show_profile']): ?>
    <div class="mini-profile" id="miniProfile">
        <div class="avatar-wrapper">
            <img src="<?= htmlspecialchars($mini_avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
        </div>
        <div class="mini-profile-card" style="background: url('<?= htmlspecialchars($cardBg) ?>') center/cover no-repeat;">
            <div class="level-circle"><?= htmlspecialchars($user_level) ?></div>
            <div class="username<?= $isVip ? ' gold-shimmer' : '' ?>"><?= htmlspecialchars($user_name) ?></div>
            <div class="divider"></div>
        </div>
        <img src="<?= htmlspecialchars($frame) ?>" alt="Frame" class="mini-profile-frame" />
    </div>
    <?php endif; ?>

    <?php if ($mini_profile_config['show_achievements']): ?>
    <div class="mini-card achievements-card" id="achievementsCard">
        <?php foreach ($selectedAchievements as $img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="Achievement">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($mini_profile_config['show_helper_effect']): ?>
    <div id="helper-effect" class="helper-effect mini-card">
        <img src="" alt="Helper">
        <div class="combo-count"></div>
    </div>
    <?php endif; ?>
</div>
<script src="assets_js/mini-profile.js"></script>
