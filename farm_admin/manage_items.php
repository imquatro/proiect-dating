<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once __DIR__ . '/../includes/db.php';
$items = $db->query('SELECT id,name,image_plant FROM farm_items ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="fa-manage-panel">
    <div class="fa-panel-window">
        <h2>Manage Items</h2>
        <select id="fa-item-select">
            <option value="">Select item</option>
            <?php foreach ($items as $item):
                $img = $item['image_plant'];
                if (strpos($img, 'img/') !== 0) {
                    $img = 'img/' . ltrim($img, '/');
                }
            ?>
            <option value="<?= htmlspecialchars($item['id']); ?>" data-image="<?= htmlspecialchars($img); ?>">
                <?= htmlspecialchars($item['name']); ?>
            </option>
            <?php endforeach; ?>
        </select>
        <div class="fa-item-preview">
            <img id="fa-item-image" src="" alt="preview" style="display:none;" />
        </div>
        <button id="fa-delete-item" disabled>Delete</button>
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
$extraJs = '<script src="farm_admin/manage-items.js"></script>';
chdir('..');
include 'template.php';
?>
