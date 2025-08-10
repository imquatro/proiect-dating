<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}

$slotTypes = [
    ['id' => 'crop', 'name' => 'Crop Plot'],
    ['id' => 'tarc', 'name' => 'Tarc Plot'],
    ['id' => 'pool', 'name' => 'Pool Plot'],
];

$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="fa-admin-panel">
    <div class="fa-panel-window">
        <h2>Add Plants & Animals</h2>
        <form id="fa-item-form" action="farm_admin/save_item.php" method="post" enctype="multipart/form-data">
            <label>Name
                <input type="text" name="name" required>
            </label>
            <label>Type
                <select name="item_type">
                    <option value="plant">Plant</option>
                    <option value="animal">Animal</option>
                </select>
            </label>
            <label>Slot Type
                <select name="slot_type">
                    <?php foreach ($slotTypes as $type): ?>
                    <option value="<?= htmlspecialchars($type['id']); ?>"><?= htmlspecialchars($type['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Planted Image
                <input type="file" name="image_plant" required>
            </label>
            <label>Ready Image
                <input type="file" name="image_ready" required>
            </label>
            <label>Product Image
                <input type="file" name="image_product" required>
            </label>
            <div class="water-field">
                <label>Water interval
                    <div class="time-input">
                        <input type="number" name="water_hours" min="0" placeholder="h">
                        <input type="number" name="water_minutes" min="0" max="59" placeholder="m">
                        <input type="number" name="water_seconds" min="0" max="59" placeholder="s">
                    </div>
                </label>
            </div>
            <div class="feed-field">
                <label>Feed interval
                    <div class="time-input">
                        <input type="number" name="feed_hours" min="0" placeholder="h">
                        <input type="number" name="feed_minutes" min="0" max="59" placeholder="m">
                        <input type="number" name="feed_seconds" min="0" max="59" placeholder="s">
                    </div>
                </label>
            </div>
            <label class="water-field">Water times
                <input type="number" name="water_times" min="0" value="0">
            </label>
            <label class="feed-field">Feed times
                <input type="number" name="feed_times" min="0" value="0">
            </label>
            <label>Price
                <input type="number" name="price" min="0" value="0">
            </label>
            <label>Production amount
                <input type="number" name="production" min="0" value="0">
            </label>
            <div class="fa-form-actions">
                <button type="submit">Save</button>
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
$extraJs = '<script src="farm_admin/admin-panel.js"></script>';
chdir('..');
include 'template.php';