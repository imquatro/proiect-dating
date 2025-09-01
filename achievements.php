<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$activePage = 'achievements';
ob_start();
include 'mini_profile.php';

$userId = $_SESSION['user_id'] ?? null;
$myAchievements = [];
if ($userId) {
    $stmt = $db->prepare('SELECT a.*, ua.selected FROM achievements a JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = ?');
    $stmt->execute([$userId]);
    $myAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div class="achievements-container">
    <div class="achievements-panel">
        <h2 class="ach-section-title">My Achievements</h2>
        <div class="ach-list" id="myAchievements">
            <?php if (empty($myAchievements)): ?>
            <div class="ach-item">
                <img src="img/achievements/default.png" alt="No achievement">
                <div class="ach-name">No achievements</div>
            </div>
            <?php else: ?>
            <?php foreach ($myAchievements as $ach): ?>
            <div class="ach-item" data-id="<?= htmlspecialchars($ach['id']); ?>">
                <img src="<?= htmlspecialchars($ach['image']); ?>" alt="<?= htmlspecialchars($ach['title']); ?>">
                <button class="ach-btn ach-apply-btn"<?= $ach['selected'] ? ' style="display:none;"' : ''; ?>>Apply</button>
                <button class="ach-btn ach-remove-btn"<?= $ach['selected'] ? '' : ' style="display:none;"'; ?>>Remove</button>
                <div class="ach-name"><?= htmlspecialchars($ach['title']); ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/achievements.css';
$extraCss = ['assets_css/mini-profile.css'];
$extraJs = '<script src="assets_js/achievements.js"></script>';
include 'template.php';
?>