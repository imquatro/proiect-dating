<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once '../includes/db.php';
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    exit('Access denied');
}
$achFile = __DIR__ . '/../includes/achievements.json';
$achievements = [];
if (is_file($achFile)) {
    $data = json_decode(file_get_contents($achFile), true);
    if (is_array($data)) {
        $achievements = $data;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['name']) && $_POST['name'] !== '') {
        $nextId = 1;
        foreach ($achievements as $a) {
            if ($a['id'] >= $nextId) {
                $nextId = $a['id'] + 1;
            }
        }
        $achievements[] = ['id' => $nextId, 'name' => $_POST['name']];
        file_put_contents($achFile, json_encode($achievements, JSON_PRETTY_PRINT));
        header('Location: manage_achievements.php');
        exit;
    }
    if (isset($_POST['delete_id']) && $_POST['delete_id'] !== '') {
        $id = (int)$_POST['delete_id'];
        $achievements = array_values(array_filter($achievements, fn($a) => $a['id'] !== $id));
        file_put_contents($achFile, json_encode($achievements, JSON_PRETTY_PRINT));
        header('Location: manage_achievements.php');
        exit;
    }
}
$ajax = isset($_GET['ajax']);
$imagePrefix = $ajax ? '' : '../';
ob_start();
?>
<div id="fa-admin-panel" data-prefix="<?= htmlspecialchars($imagePrefix); ?>">
    <div class="fa-panel-window">
        <h2>Manage Achievements</h2>
        <form method="post">
            <label>Achievement Name
                <input type="text" name="name" required>
            </label>
            <div class="fa-form-actions">
                <button type="submit">Add</button>
            </div>
        </form>
        <form method="post">
            <label>Select Achievement
                <select name="delete_id" id="achievementSelect">
                    <option value="">Select</option>
                    <?php foreach ($achievements as $a): ?>
                    <option value="<?= htmlspecialchars($a['id']); ?>"><?= htmlspecialchars($a['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="fa-form-actions">
                <button type="submit" id="deleteAchievement" disabled>Delete</button>
            </div>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$activePage = 'diverse';
$pageCss = 'farm_admin/admin-panel.css';
$extraJs = '<script src="farm_admin/achievements.js"></script>';
chdir('..');
include 'template.php';
?>