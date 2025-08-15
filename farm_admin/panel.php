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

require_once '../includes/db.php';
$items = $db->query('SELECT id,name,image_plant,price FROM farm_items ORDER BY name')
    ->fetchAll(PDO::FETCH_ASSOC);

$ajax = isset($_GET['ajax']);
ob_start();
?>
<div id="fa-admin-panel">
    <div class="fa-panel-window">
        <div class="fa-tab-header">
            <button class="active" data-tab="add">Add Items</button>
            <button data-tab="edit">Edit Items</button>
            <button data-tab="delete">Delete Items</button>
        </div>
        <div class="fa-tab-content active" id="fa-tab-add">
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
        <div class="fa-tab-content" id="fa-tab-edit">
            <h2>Edit Items</h2>
            <div class="fa-edit-grid">
                <?php foreach ($items as $item):
                    $img = $item['image_plant'];
                    if (strpos($img, 'img/') !== 0) {
                        $img = 'img/' . ltrim($img, '/');
                    }
                ?>
                <div class="fa-edit-item" data-id="<?= htmlspecialchars($item['id']); ?>">
                    <img src="<?= htmlspecialchars($img); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="qs-info">
                        <span class="qs-price"><?= htmlspecialchars($item['price']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <form id="fa-edit-form" action="farm_admin/update_item.php" method="post" style="display:none;">
                <input type="hidden" name="id">
                <input type="hidden" name="current_image_plant">
                <input type="hidden" name="current_image_product">
                <input type="hidden" name="barn_capacity">
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
        <div class="fa-tab-content" id="fa-tab-delete">
            <h2>Delete Items</h2>
            <div class="fa-delete-grid">
                <?php foreach ($items as $item):
                    $img = $item['image_plant'];
                    if (strpos($img, 'img/') !== 0) {
                        $img = 'img/' . ltrim($img, '/');
                    }
                ?>
                <div class="fa-delete-item" data-id="<?= htmlspecialchars($item['id']); ?>">
                    <img src="<?= htmlspecialchars($img); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="qs-info">
                        <span class="qs-price"><?= htmlspecialchars($item['price']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button id="fa-delete-item-btn" disabled>Delete</button>
        </div>
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
$hideNav = true;
chdir('..');
include 'template.php';