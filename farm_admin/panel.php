<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit('Access denied');
}
require_once '../includes/db.php';
require_once '../includes/helper_images.php';
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    exit('Access denied');
}
$slotTypes = [
    ['id' => 'crop', 'name' => 'Crop Plot'],
    ['id' => 'tarc', 'name' => 'Tarc Plot'],
    ['id' => 'pool', 'name' => 'Pool Plot'],
];

$items = $db->query('SELECT id,name,image_plant,price FROM farm_items ORDER BY name')
    ->fetchAll(PDO::FETCH_ASSOC);
$frameDir = __DIR__ . '/../img/vip_frames';
$vipFrames = array_map('basename', array_filter(glob($frameDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));
$cardDir = __DIR__ . '/../img/vip_cards';
$vipCards = array_map('basename', array_filter(glob($cardDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));

 $helpers = $db->query('SELECT id,name,image,message_file,waters,feeds,harvests FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
 foreach ($helpers as &$h) {
     $h['image_src'] = resolve_helper_image($h['image']);
 }
 unset($h);

$nextAchId = (int)$db->query('SELECT COALESCE(MAX(id),0)+1 FROM achievements')->fetchColumn();
$achievements = $db->query('SELECT id, title FROM achievements ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

$versionFile = __DIR__ . '/../version.txt';
$currentVersion = is_file($versionFile) ? trim(file_get_contents($versionFile)) : 'unknown';

$ajax = isset($_GET['ajax']);
$imagePrefix = $ajax ? '' : '../';
ob_start();
?>
<div id="fa-admin-panel" data-prefix="<?= htmlspecialchars($imagePrefix); ?>">
    <div class="fa-panel-window">
        <div class="fa-tab-header">
            <button class="active" data-tab="add">Add Items</button>
            <button data-tab="edit">Edit Items</button>
            <button data-tab="delete">Delete Items</button>
            <button data-tab="vip">VIP Items</button>
            <button data-tab="ach">Achievements</button>
            <button data-tab="add-helper">Add Helper</button>
            <button data-tab="edit-helper">Edit Helper</button>
            <button data-tab="version">Update Version</button>
        </div>
        <div class="fa-tab-content active" id="fa-tab-add">
            <h2>Add Plants & Animals</h2>
            <form id="fa-item-form" action="farm_admin/save_item.php" method="post">
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
                <label>Image Name
                    <input type="text" name="image_name" required>
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
                <label>Sell Price
                    <input type="number" name="sell_price" min="0" value="0">
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
                    $img = 'img/' . basename($item['image_plant']);
                ?>
                <div class="fa-edit-item" data-id="<?= htmlspecialchars($item['id']); ?>">
                    <img src="<?= htmlspecialchars($imagePrefix . $img); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="qs-info">
                        <span class="qs-price"><?= htmlspecialchars($item['price']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
             <form id="fa-edit-form" action="farm_admin/update_item.php" method="post" style="display:none;">
                <input type="hidden" name="id">
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
                <label>Image Name
                    <input type="text" name="image_name" required>
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
                <label>Sell Price
                    <input type="number" name="sell_price" min="0" value="0">
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
                    $img = 'img/' . basename($item['image_plant']);
                ?>
                <div class="fa-delete-item" data-id="<?= htmlspecialchars($item['id']); ?>">
                    <img src="<?= htmlspecialchars($imagePrefix . $img); ?>" alt="<?= htmlspecialchars($item['name']); ?>">
                    <div class="qs-info">
                        <span class="qs-price"><?= htmlspecialchars($item['price']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button id="fa-delete-item-btn" disabled>Delete</button>
        </div>

        <div class="fa-tab-content" id="fa-tab-vip">
            <h2>Add VIP Frames/Cards</h2>
            <form id="fa-vip-form" action="farm_admin/save_vip.php" method="post">
                <label>Type
                    <select name="vip_type">
                        <option value="frame">Frame</option>
                        <option value="card">Card</option>
                    </select>
                </label>
                <label>Image Name
                    <input type="text" name="image_name" required>
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Add</button>
                </div>
            </form>
            <h2>Delete VIP Frames</h2>
            <form class="fa-delete-vip-form" action="farm_admin/delete_vip.php" method="post">
                <input type="hidden" name="vip_type" value="frame">
                <label>Frame Name
                    <select name="vip_name">
                        <?php foreach ($vipFrames as $f): ?>
                        <option value="<?= htmlspecialchars($f); ?>"><?= htmlspecialchars($f); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Delete</button>
                </div>
            </form>
            <h2>Delete VIP Cards</h2>
            <form class="fa-delete-vip-form" action="farm_admin/delete_vip.php" method="post">
                <input type="hidden" name="vip_type" value="card">
                <label>Card Name
                    <select name="vip_name">
                        <?php foreach ($vipCards as $c): ?>
                        <option value="<?= htmlspecialchars($c); ?>"><?= htmlspecialchars($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Delete</button>
                </div>
            </form>
        </div>
        <div class="fa-tab-content" id="fa-tab-ach">
            <h2>Add Achievement</h2>
            <form id="fa-achievement-form" action="farm_admin/save_achievement.php" method="post">
                <label>ID
                    <input type="number" name="id" value="<?= htmlspecialchars($nextAchId); ?>" readonly>
                </label>
                <label>Title
                    <input type="text" name="title" required>
                </label>
                <label>Harvest Count
                    <input type="number" name="harvest" min="0" value="0">
                </label>
                <label>Sales Count
                    <input type="number" name="sales" min="0" value="0">
                </label>
                <label>Level
                    <input type="number" name="level" min="0" value="0">
                </label>
                <label>XP
                    <input type="number" name="xp" min="0" value="0">
                </label>
                <label>Account Age (years)
                    <input type="number" name="years" min="0" value="0">
                </label>
                <label>Item
                    <select name="item_id">
                        <option value="">None</option>
                        <?php foreach ($items as $item): ?>
                        <option value="<?= htmlspecialchars($item['id']); ?>"><?= htmlspecialchars($item['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Image Name
                    <input type="text" name="image_name" required>
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Save</button>
                </div>
            </form>
            <h2>Delete Achievement</h2>
            <form id="fa-delete-achievement" action="farm_admin/delete_achievement.php" method="post">
                <label>Select Achievement
                    <select name="id" id="achievementSelect">
                        <option value="">Select</option>
                        <?php foreach ($achievements as $a): ?>
                        <option value="<?= htmlspecialchars($a['id']); ?>"><?= htmlspecialchars($a['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <div class="fa-form-actions">
                    <button type="submit" id="deleteAchievement" disabled>Delete</button>
                </div>
            </form>
        </div>
        <div class="fa-tab-content" id="fa-tab-add-helper">
            <h2>Add Helper</h2>
            <form id="fa-helper-form" action="farm_admin/save_helper.php" method="post">
                <label>Name
                    <input type="text" name="name" required>
                </label>
                <label>Image
                    <input type="text" name="image" required>
                </label>
                <label>Message File
                    <input type="text" name="message_file" required>
                </label>
                <label>Watering per day
                    <input type="number" name="waters" min="0" value="0">
                </label>
                <label>Feeding per day
                    <input type="number" name="feeds" min="0" value="0">
                </label>
                <label>Harvesting per day
                    <input type="number" name="harvests" min="0" value="0">
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Save</button>
                </div>
            </form>
        </div>
        <div class="fa-tab-content" id="fa-tab-edit-helper">
            <h2>Edit Helper</h2>
            <div class="fa-edit-helper-grid">
                <?php foreach ($helpers as $h): ?>
                <div class="fa-helper-item" data-id="<?= htmlspecialchars($h['id']); ?>" data-name="<?= htmlspecialchars($h['name']); ?>" data-image="<?= htmlspecialchars($h['image']); ?>" data-message="<?= htmlspecialchars($h['message_file']); ?>" data-waters="<?= htmlspecialchars($h['waters']); ?>" data-feeds="<?= htmlspecialchars($h['feeds']); ?>" data-harvests="<?= htmlspecialchars($h['harvests']); ?>">
                    <img src="<?= htmlspecialchars($imagePrefix . $h['image_src']); ?>" alt="<?= htmlspecialchars($h['name']); ?>">
                    <span><?= htmlspecialchars($h['name']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <form id="fa-helper-edit-form" action="farm_admin/update_helper.php" method="post" style="display:none;">
                <input type="hidden" name="id">
                <label>Name
                    <input type="text" name="name" required>
                </label>
                <label>Image
                    <input type="text" name="image" required>
                </label>
                <label>Message File
                    <input type="text" name="message_file" required>
                </label>
                <label>Watering per day
                    <input type="number" name="waters" min="0" value="0">
                </label>
                <label>Feeding per day
                    <input type="number" name="feeds" min="0" value="0">
                </label>
                <label>Harvesting per day
                    <input type="number" name="harvests" min="0" value="0">
                </label>
                <div class="fa-form-actions">
                    <button type="submit">Save</button>
                </div>
            </form>
        </div>
        <div class="fa-tab-content" id="fa-tab-version">
            <h2>Cache Version</h2>
            <p>Current version: <span id="fa-current-version"><?= htmlspecialchars($currentVersion); ?></span></p>
            <button id="fa-update-version">Update Version</button>
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
$extraJs = '<script src="farm_admin/admin-panel.js"></script><script src="farm_admin/achievements.js"></script>';
$baseHref = '../';
$hideNav = true;
chdir('..');
include 'template.php';
