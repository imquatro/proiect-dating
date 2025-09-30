<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/includes/db.php';

$visitId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($visitId <= 0) {
    header('Location: friends.php');
    exit;
}

$stmt = $db->prepare('SELECT username FROM users WHERE id = ?');
$stmt->execute([$visitId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: friends.php');
    exit;
}
$username = $user['username'];

$myStmt = $db->prepare('SELECT a.* FROM achievements a JOIN user_achievements ua ON ua.achievement_id = a.id WHERE ua.user_id = ?');
$myStmt->execute([$visitId]);
$myAchievements = $myStmt->fetchAll(PDO::FETCH_ASSOC);

$allAchievements = $db->query('SELECT * FROM achievements')->fetchAll(PDO::FETCH_ASSOC);
$achievedIds = array_column($myAchievements, 'id');

ob_start();
?>
<div class="achievements-container">
    <div class="achievements-panel">
        <div class="ach-columns">
            <div class="ach-column">
                <h2 class="ach-section-title"><?= htmlspecialchars($username) ?>'s Achievements</h2>
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
$extraJs = '<script>window.isVisitor = true; window.visitId = ' . $visitId . '; window.profileOwnerId = ' . $visitId . ';</script><script src="assets_js/achievements.js"></script>';
$activePage = '';
include 'template.php';
?>