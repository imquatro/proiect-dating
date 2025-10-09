<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

// Check admin
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$isAdmin = $stmt->fetchColumn() == 1;

if (!$isAdmin) {
    header('Location: welcome.php');
    exit;
}

// Load admin panel data - exact ca în panel.php
require_once __DIR__ . '/includes/helper_images.php';

$slotTypes = [
    ['id' => 'crop', 'name' => 'Crop Plot'],
    ['id' => 'tarc', 'name' => 'Tarc Plot'],
    ['id' => 'pool', 'name' => 'Pool Plot'],
];

$items = $db->query('SELECT id,name,image_plant,price FROM farm_items ORDER BY name')
    ->fetchAll(PDO::FETCH_ASSOC);
$frameDir = __DIR__ . '/img/vip_frames';
$vipFrames = array_map('basename', array_filter(glob($frameDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));
$cardDir = __DIR__ . '/img/vip_cards';
$vipCards = array_map('basename', array_filter(glob($cardDir.'/*.{png,gif,jpg,jpeg}', GLOB_BRACE)));

$helpers = $db->query('SELECT id,name,image,message_file,waters,feeds,harvests FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($helpers as &$h) {
    $h['image_src'] = resolve_helper_image($h['image']);
}
unset($h);

$nextAchId = (int)$db->query('SELECT COALESCE(MAX(id),0)+1 FROM achievements')->fetchColumn();
$achievements = $db->query('SELECT id, title FROM achievements ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

$versionFile = __DIR__ . '/version.txt';
$currentVersion = is_file($versionFile) ? trim(file_get_contents($versionFile)) : 'unknown';

$imagePrefix = '';

ob_start();
?>
<style>
/* Settings overlay - NU acoperă meniurile template-ului */
.settings-overlay {
    position: fixed;
    top: 60px;
    left: 0;
    width: 100%;
    height: calc(100vh - 120px);
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

/* Asigură că meniurile template-ului rămân vizibile */
.top-bar {
    z-index: 1001 !important;
}

.bottom-nav {
    z-index: 1001 !important;
}

.settings-panel {
    width: 90%;
    max-width: 600px;
    height: calc(100vh - 140px);
    max-height: calc(100vh - 140px);
    background: url('img/bg2.png') center/cover no-repeat !important;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.6);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.settings-header {
    background: #ffe9a3;
    padding: 15px 20px;
    color: #4a3a00;
    border-bottom: 2px solid #f6cf49;
    border-radius: 12px 12px 0 0;
}

.settings-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.nav-btn {
    padding: 8px 16px;
    background: #ffe9a3;
    border: 1px solid #f6cf49;
    border-radius: 6px;
    color: #6c4e09;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    font-size: 14px;
    flex: 1;
    text-align: center;
    cursor: pointer;
}

.nav-btn:hover {
    background: #f6cf49;
    border-color: #e6b800;
}

.nav-btn.active {
    background: #e6b800;
    border-color: #d4a000;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: url('img/bg2.png') center/cover no-repeat !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.settings-content::-webkit-scrollbar {
    display: none !important;
}

/* Admin Panel CSS - exact ca farm_admin/admin-panel.css */
.fa-tab-header {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}

.fa-tab-header button {
    flex: 1;
    padding: 8px;
    cursor: pointer;
    background: #ddd;
    border: none;
    border-radius: 4px;
    min-width: 70px;
    font-size: 12px;
}

.fa-tab-header button.active {
    background: linear-gradient(90deg, #8bc34a, #4caf50);
    color: #fff;
}

.fa-tab-content {
    display: none;
}

.fa-tab-content.active {
    display: block;
}

#fa-item-form label,
#fa-edit-form label,
#fa-achievement-form label,
#fa-helper-form label,
#fa-helper-edit-form label {
    display: block;
    margin-bottom: 10px;
    color: #fff;
}

#fa-item-form input,
#fa-item-form select,
#fa-edit-form input,
#fa-edit-form select,
#fa-achievement-form input,
#fa-achievement-form select,
#fa-helper-form input,
#fa-helper-form select,
#fa-helper-edit-form input,
#fa-helper-edit-form select {
    width: 100%;
    box-sizing: border-box;
    margin-top: 4px;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background: #fff;
}

.time-input {
    display: flex;
    gap: 5px;
}

.time-input input {
    width: 33%;
}

.fa-form-actions {
    text-align: right;
    margin-top: 10px;
}

.fa-form-actions button,
#fa-tab-delete button,
#fa-update-version {
    padding: 8px 16px;
    background: linear-gradient(90deg, #8bc34a, #4caf50);
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.fa-edit-grid,
.fa-delete-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin-bottom: 10px;
}

.fa-edit-helper-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin-bottom: 10px;
}

.fa-edit-item,
.fa-delete-item,
.fa-helper-item {
    position: relative;
    width: 100%;
    padding-top: 100%;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    cursor: pointer;
    box-sizing: border-box;
    border: 3px solid transparent;
}

.fa-edit-item img,
.fa-delete-item img,
.fa-helper-item img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.fa-edit-item.selected,
.fa-delete-item.selected,
.fa-helper-item.selected {
    border-color: #4caf50;
    box-shadow: 0 0 10px #4caf50;
}

.fa-edit-item .qs-info,
.fa-delete-item .qs-info {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 5px 0;
    font-size: 12px;
}

h2 {
    color: #fff;
    margin-top: 0;
    font-size: 18px;
}

p {
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .settings-panel {
        width: 95%;
        height: 95vh;
    }
    
    .settings-nav {
        gap: 6px;
    }
    
    .nav-btn {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .fa-tab-header {
        flex-direction: column;
    }
}
</style>

<div class="settings-overlay">
    <div class="settings-panel">
        <div class="settings-header">
            <h2 style="margin: 0; text-align: center;">Admin Panel</h2>
            
            <div class="settings-nav">
                <a href="settings_profile.php" class="nav-btn">Profile</a>
                <a href="settings_bank.php" class="nav-btn">Bank</a>
                <a href="settings_helpers.php" class="nav-btn">Helpers</a>
                <a href="settings_admin.php" class="nav-btn active">Admin Panel</a>
                <a href="#" class="nav-btn" id="logoutBtn">Logout</a>
            </div>
        </div>

        <div class="settings-content" id="fa-admin-panel" data-prefix="<?= htmlspecialchars($imagePrefix); ?>">
            <!-- Admin Panel Content - exact ca farm_admin/panel.php -->
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
                
                <!-- Add Items Tab -->
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

                <!-- Edit Items Tab -->
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

                <!-- Delete Items Tab -->
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

                <!-- VIP Items Tab -->
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

                <!-- Achievements Tab -->
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

                <!-- Add Helper Tab -->
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

                <!-- Edit Helper Tab -->
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

                <!-- Version Tab -->
                <div class="fa-tab-content" id="fa-tab-version">
                    <h2>Cache Version</h2>
                    <p>Current version: <span id="fa-current-version"><?= htmlspecialchars($currentVersion); ?></span></p>
                    <button id="fa-update-version">Update Version</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="farm_admin/admin-panel.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Close overlay when clicking outside - revine la welcome.php
    const overlay = document.querySelector('.settings-overlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                window.location.href = 'welcome.php';
            }
        });
    }

    // Logout confirmation
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();

$pageTitle = 'Admin Panel';
$pageCss = '';

include 'template.php';
?>
