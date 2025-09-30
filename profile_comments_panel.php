<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit;
}

$visitId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$panelUserId = $visitId > 0 ? $visitId : (int)$_SESSION['user_id'];
$miniHtml = '';

if ($visitId > 0 && $visitId !== (int)$_SESSION['user_id']) {
    require_once __DIR__ . '/includes/db.php';
    $stmt = $db->prepare('SELECT username, gallery, level, vip, vip_frame, vip_card FROM users WHERE id = ?');
    $stmt->execute([$visitId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $avatar = 'default-avatar.png';
        if (!empty($user['gallery'])) {
            $gal = array_filter(explode(',', $user['gallery']));
            if (!empty($gal)) {
                $candidate = __DIR__ . '/uploads/' . $visitId . '/' . trim($gal[0]);
                if (is_file($candidate)) {
                    $avatar = 'uploads/' . $visitId . '/' . trim($gal[0]);
                }
            }
        }
        $username = $user['username'];
        $level = isset($user['level']) ? (int)$user['level'] : 1;
        $isVip = !empty($user['vip']);
        $frame = 'img/frames/defaultframe.png';
        $cardBg = 'img/bg2.png';
        if (!empty($user['vip_frame']) && is_file(__DIR__ . '/img/vip_frames/' . $user['vip_frame'])) {
            $frame = 'img/vip_frames/' . $user['vip_frame'];
        }
        if (!empty($user['vip_card']) && is_file(__DIR__ . '/img/vip_cards/' . $user['vip_card'])) {
            $cardBg = 'img/vip_cards/' . $user['vip_card'];
        }
        ob_start();
        ?>
        <div class="mini-cards-row">
            <div class="mini-profile" id="miniProfile">
                <div class="avatar-wrapper">
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
                </div>
                <div class="mini-profile-card" style="background: url('<?= htmlspecialchars($cardBg) ?>') center/cover no-repeat;">
                    <div class="level-circle"><?= htmlspecialchars($level) ?></div>
                    <div class="username<?= $isVip ? ' gold-shimmer' : '' ?>"><?= htmlspecialchars($username) ?></div>
                    <div class="divider"></div>
                </div>
                <img src="<?= htmlspecialchars($frame) ?>" alt="Frame" class="mini-profile-frame" />
            </div>
        </div>
        <?php
        $miniHtml = ob_get_clean();
    }
}

if ($miniHtml === '') {
    ob_start();
    $mini_profile_config = [
        'show_helpers' => false,
        'show_profile' => true,
        'show_achievements' => false,
        'show_helper_effect' => false,
        'center_single' => false,
    ];
    include 'mini_profile.php';
    $miniHtml = ob_get_clean();
}

$miniHtml = str_replace('id="miniProfile"', 'id="panelMiniProfile"', $miniHtml);
$miniHtml = preg_replace('#<script[^>]*mini-profile.js[^>]*></script>#', '', $miniHtml);
?>
<div id="profile-comments-panel" data-user-id="<?= (int)$panelUserId ?>">
    <?= $miniHtml ?>
    <div class="helpers-bar" id="helper-avatars"></div>
    <div class="comments-section">
        <div class="comments-list" id="comments-list"></div>
        <form id="comment-form" autocomplete="off">
            <input type="text" id="comment-input" placeholder="Scrie un comentariu...">
            <button type="submit">Trimite</button>
        </form>
    </div>
</div>
