<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$activePage = 'achievements';
ob_start();

$userId = $_SESSION['user_id'] ?? null;
$myAchievements = [];
$selectedId = null;
if ($userId) {
    $stmt = $db->prepare('SELECT a.*, ua.selected FROM achievements a JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = ?');
    $stmt->execute([$userId]);
    $myAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($myAchievements as $ma) {
        if (!empty($ma['selected'])) { $selectedId = $ma['id']; }
    }
}
$allAchievements = $db->query('SELECT * FROM achievements')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="achievements-container">
    <div class="achievements-panel">
        <h2 class="ach-section-title">My Achievements</h2>
        <div class="ach-list" id="myAchievements">
            <?php foreach ($myAchievements as $ach): ?>
            <div class="ach-item" data-id="<?= htmlspecialchars($ach['id']); ?>">
                <img src="<?= htmlspecialchars($ach['image']); ?>" alt="<?= htmlspecialchars($ach['title']); ?>">
                <div class="ach-name"><?= htmlspecialchars($ach['title']); ?></div>
                <button class="apply-ach-btn"<?= $ach['selected'] ? ' disabled' : ''; ?>><?= $ach['selected'] ? 'Selected' : 'Apply'; ?></button>
            </div>
            <?php endforeach; ?>
        </div>
        <h2 class="ach-section-title">All Achievements</h2>
        <div class="ach-list" id="allAchievements">
            <?php foreach ($allAchievements as $ach): ?>
            <div class="ach-item" data-id="<?= htmlspecialchars($ach['id']); ?>">
                <img src="<?= htmlspecialchars($ach['image']); ?>" alt="<?= htmlspecialchars($ach['title']); ?>">
                <div class="ach-name"><?= htmlspecialchars($ach['title']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/achievements.css';
$extraJs = '<script src="assets_js/achievements.js"></script>';
include 'template.php';
?>