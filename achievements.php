<?php
session_start();
require_once __DIR__ . '/includes/db.php';
$activePage = 'achievements';
ob_start();
include 'mini_profile.php';

$sessionId = $_SESSION['user_id'] ?? null;
$viewId = isset($_GET['id']) ? (int)$_GET['id'] : $sessionId;
$isVisitor = $sessionId && $viewId && $viewId !== $sessionId;
$username = '';
if ($isVisitor) {
    $uStmt = $db->prepare('SELECT username FROM users WHERE id = ?');
    $uStmt->execute([$viewId]);
    $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
    $username = $uRow['username'] ?? '';
}
$myAchievements = [];
if ($viewId) {
    $stmt = $db->prepare('SELECT a.*, ua.selected FROM achievements a JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = ?');
    $stmt->execute([$viewId]);
    $myAchievements = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$allAchievements = $db->query('SELECT * FROM achievements')->fetchAll(PDO::FETCH_ASSOC);
$achievedIds = array_column($myAchievements, 'id');
?>
<div class="achievements-container">
    <div class="achievements-panel">
        <div class="ach-columns">
            <div class="ach-column">
                <h2 class="ach-section-title"><?= $isVisitor ? htmlspecialchars($username) . "'s Achievements" : 'My Achievements'; ?></h2>
                <div class="ach-list" id="myAchievements">
                    <?php if (empty($myAchievements)): ?>
                    <div class="ach-item">
                        <img src="img/achievements/default.png" alt="No achievement">
                        <div class="ach-name">No achievements</div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($myAchievements as $ach): ?>
                    <div class="ach-item" data-id="<?= htmlspecialchars($ach['id']); ?>">
                        <div class="ach-img-wrapper">
                            <img src="<?= htmlspecialchars($ach['image']); ?>" alt="<?= htmlspecialchars($ach['title']); ?>">
                            <?php if (!$isVisitor): ?>
                            <button class="ach-btn ach-apply-btn"<?= $ach['selected'] ? ' style="display:none;"' : ''; ?>>Apply</button>
                            <button class="ach-btn ach-remove-btn"<?= $ach['selected'] ? '' : ' style="display:none;"'; ?>>Remove</button>
                            <?php endif; ?>
                        </div>
                        <div class="ach-name"><?= htmlspecialchars($ach['title']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="ach-column">
                <h2 class="ach-section-title">All Achievements</h2>
                <div class="ach-list" id="allAchievements">
                    <?php if (empty($allAchievements)): ?>
                    <div class="ach-item">
                        <img src="img/achievements/default.png" alt="No achievement">
                        <div class="ach-name">No achievements</div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($allAchievements as $ach): ?>
                    <div class="ach-item<?= in_array($ach['id'], $achievedIds) ? ' achieved' : ''; ?>" data-id="<?= htmlspecialchars($ach['id']); ?>">
                        <img src="<?= htmlspecialchars($ach['image']); ?>" alt="<?= htmlspecialchars($ach['title']); ?>">
                        <div class="ach-name"><?= htmlspecialchars($ach['title']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="achDetailOverlay" class="ach-detail-overlay" style="display:none;">
    <div class="ach-detail-card">
        <img id="achDetailImage" src="" alt="Achievement">
        <div class="ach-progress-bar"><div id="achProgressFill" class="ach-progress-fill"></div></div>
        <div id="achProgressText" class="ach-progress-text"></div>
        <div id="achDetailText" class="ach-detail-text"></div>
        <button id="achDetailClose" class="ach-detail-close" aria-label="Close">&times;</button>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/achievements.css';
$extraCss = ['assets_css/mini-profile.css'];
$extraJs = '<script>window.isVisitor = ' . ($isVisitor ? 'true' : 'false') . '; window.visitId = ' . ($isVisitor ? $viewId : 'null') . ';</script><script src="assets_js/achievements.js"></script>';
include 'template.php';
?>