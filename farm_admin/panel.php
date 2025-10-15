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
            <button data-tab="add-users">Add Users</button>
            <button data-tab="admin-grades">Admin Grades</button>
            <button data-tab="pvp-system">PVP SYSTEM</button>
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
        <div class="fa-tab-content" id="fa-tab-add-users">
            <h2>User Management</h2>
            <div class="user-creation-tabs">
                <button class="user-tab-btn active" data-usertab="auto">Auto Create</button>
                <button class="user-tab-btn" data-usertab="manual">Manual Create</button>
                <button class="user-tab-btn" data-usertab="password">Update Passwords</button>
            </div>
            
            <!-- Auto Create Users -->
            <div class="user-tab-content active" id="user-tab-auto">
                <h3>Auto Create Users</h3>
                <form id="fa-auto-users-form" action="farm_admin/create_users_auto.php" method="post">
                    <label>Number of Users to Create
                        <input type="number" name="user_count" min="1" value="1" required>
                    </label>
                    <label>Default Password for New Users
                        <div class="password-input-container">
                            <input type="password" id="default_password_input" name="default_password" value="password123" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('default_password_input')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="current-password-indicator">
                            <span class="current-password-label">Current Password:</span>
                            <span class="current-password-text" id="current_password_display">password123</span>
                        </div>
                    </label>
                    <div class="fa-form-actions">
                        <button type="submit">Create Users</button>
                    </div>
                </form>
                <div id="auto-users-result" class="users-result"></div>
            </div>
            
            <!-- Manual Create User -->
            <div class="user-tab-content" id="user-tab-manual">
                <h3>Manual Create User</h3>
                <form id="fa-manual-user-form" action="farm_admin/create_user_manual.php" method="post">
                    <label>Email
                        <input type="email" name="email" required>
                    </label>
                    <label>Username
                        <input type="text" name="username" required>
                    </label>
                    <label>Password
                        <input type="password" name="password" required>
                    </label>
                    <label>Age
                        <input type="number" name="age" min="18" max="99" required>
                    </label>
                    <label>Country
                        <input type="text" name="country" required>
                    </label>
                    <label>City
                        <input type="text" name="city" required>
                    </label>
                    <label>Gender
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="masculin">Masculin</option>
                            <option value="feminin">Feminin</option>
                        </select>
                    </label>
                    <div class="fa-form-actions">
                        <button type="submit">Create User</button>
                    </div>
                </form>
                <div id="manual-user-result" class="users-result"></div>
            </div>
            
            <!-- Update Passwords -->
            <div class="user-tab-content" id="user-tab-password">
                <h3>Update Auto-Created User Passwords</h3>
                <p>This will update the password for ALL auto-created users (created from admin panel) to the new password below.</p>
                <form id="fa-update-passwords-form" action="farm_admin/update_all_passwords.php" method="post">
                    <label>New Password for All Users
                        <div class="password-input-container">
                            <input type="password" id="new_password_input" name="new_password" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password_input')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </label>
                    <label>Confirm New Password
                        <div class="password-input-container">
                            <input type="password" id="confirm_password_input" name="confirm_password" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password_input')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </label>
                    <div class="fa-form-actions">
                        <button type="submit">Update All Passwords</button>
                    </div>
                </form>
                <div id="update-passwords-result" class="users-result"></div>
            </div>
        </div>
        <div class="fa-tab-content" id="fa-tab-admin-grades">
            <h2>Admin Grade Management</h2>
            <div class="admin-grades-tabs">
                <button class="grades-tab-btn active" data-gradestab="manage">Manage Grades</button>
                <button class="grades-tab-btn" data-gradestab="permissions">Permissions</button>
                <button class="grades-tab-btn" data-gradestab="logs">Activity Logs</button>
            </div>
            
            <!-- Manage Grades -->
            <div class="grades-tab-content active" id="grades-tab-manage">
                <h3>User Grade Management</h3>
                <div class="grade-levels-info">
                    <h4>Grade Levels:</h4>
                    <ul>
                        <li><strong>1 - SUPER_ADMIN:</strong> Full system access, can manage other admins</li>
                        <li><strong>2 - ADMIN:</strong> Access to admin panel, can create test accounts</li>
                        <li><strong>3 - MODERATOR:</strong> Can manage users, limited admin access</li>
                        <li><strong>4 - HELPER:</strong> Can help users, view limited statistics</li>
                        <li><strong>5 - USER:</strong> Normal user, no admin access</li>
                    </ul>
                </div>
                
                <div class="grade-search-section">
                    <label>Search Users by Username or Email
                        <input type="text" id="grade-search-input" placeholder="Enter username or email...">
                    </label>
                    <button id="search-users-btn">Search</button>
                </div>
                
                <div id="users-list" class="users-list"></div>
                
                <div id="grade-change-form" class="grade-change-form" style="display: none;">
                    <h4>Change User Grade</h4>
                    <form id="fa-grade-change-form" action="farm_admin/change_user_grade.php" method="post">
                        <input type="hidden" id="selected-user-id" name="user_id">
                        <div class="user-info-display">
                            <p><strong>User:</strong> <span id="selected-username"></span></p>
                            <p><strong>Current Grade:</strong> <span id="selected-current-grade"></span></p>
                        </div>
                        <label>New Grade
                            <select name="new_admin_level" id="new-admin-level" required>
                                <option value="1">1 - SUPER_ADMIN</option>
                                <option value="2">2 - ADMIN</option>
                                <option value="3">3 - MODERATOR</option>
                                <option value="4">4 - HELPER</option>
                                <option value="5">5 - USER</option>
                            </select>
                        </label>
                        <label>Reason for Change
                            <input type="text" name="reason" placeholder="Enter reason for grade change..." required>
                        </label>
                        <div class="fa-form-actions">
                            <button type="submit">Change Grade</button>
                            <button type="button" id="cancel-grade-change">Cancel</button>
                        </div>
                    </form>
                </div>
                <div id="grade-change-result" class="users-result"></div>
            </div>
            
            <!-- Permissions -->
            <div class="grades-tab-content" id="grades-tab-permissions">
                <h3>Permission System</h3>
                <div class="permissions-grid">
                    <div class="permission-level">
                        <h4>SUPER_ADMIN (Level 1)</h4>
                        <ul>
                            <li>✅ Manage all other admins</li>
                            <li>✅ Change any user grade</li>
                            <li>✅ Full admin panel access</li>
                            <li>✅ System configuration</li>
                            <li>✅ View all logs</li>
                        </ul>
                    </div>
                    <div class="permission-level">
                        <h4>ADMIN (Level 2)</h4>
                        <ul>
                            <li>✅ Create test accounts</li>
                            <li>✅ Manage items & achievements</li>
                            <li>✅ Update auto account passwords</li>
                            <li>✅ View statistics</li>
                            <li>❌ Manage other admins</li>
                        </ul>
                    </div>
                    <div class="permission-level">
                        <h4>MODERATOR (Level 3)</h4>
                        <ul>
                            <li>✅ Manage normal users</li>
                            <li>✅ View user reports</li>
                            <li>✅ Limited statistics</li>
                            <li>❌ Admin panel access</li>
                            <li>❌ Create test accounts</li>
                        </ul>
                    </div>
                    <div class="permission-level">
                        <h4>HELPER (Level 4)</h4>
                        <ul>
                            <li>✅ Help users with questions</li>
                            <li>✅ View basic statistics</li>
                            <li>❌ Manage users</li>
                            <li>❌ Admin functions</li>
                            <li>❌ Access to admin panel</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Activity Logs -->
            <div class="grades-tab-content" id="grades-tab-logs">
                <h3>Admin Activity Logs</h3>
                <div class="logs-controls">
                    <label>Filter by Action
                        <select id="log-action-filter">
                            <option value="">All Actions</option>
                            <option value="grade_change">Grade Changes</option>
                            <option value="user_creation">User Creation</option>
                            <option value="password_update">Password Updates</option>
                            <option value="admin_login">Admin Login</option>
                        </select>
                    </label>
                    <label>Filter by Admin
                        <select id="log-admin-filter">
                            <option value="">All Admins</option>
                        </select>
                    </label>
                    <button id="refresh-logs-btn">Refresh Logs</button>
                </div>
                <div id="activity-logs" class="activity-logs"></div>
            </div>
        </div>
        
        <!-- PVP System Tab -->
        <div class="fa-tab-content" id="fa-tab-pvp-system">
            <h2>PVP System Management</h2>
            
            <!-- Event Control Section -->
        <div class="pvp-admin-section">
            <h3>Event Control</h3>
            <div class="pvp-admin-buttons">
                <button id="startPvpEvent" class="pvp-admin-btn start-event">
                    <i class="fas fa-play"></i> START EVENT
                </button>
                <button id="stopPvpEvent" class="pvp-admin-btn stop-event">
                    <i class="fas fa-stop"></i> STOP EVENT
                </button>
            </div>
            <p style="color: #888; font-size: 12px; margin-top: 10px;">
                ⚠️ START: Cleans all battles + Enables system + Starts tournaments<br>
                ⚠️ STOP: Cleans all battles + Stops system
            </p>
        </div>
            
            <!-- Tournament Loop Control Section -->
            <div class="pvp-admin-section">
                <h3>Tournament Loop</h3>
                <div class="toggle-switch-container">
                    <label class="toggle-switch">
                        <input type="checkbox" id="pvp-loop-toggle-input" data-state="disabled">
                        <span class="toggle-knob"></span>
                    </label>
                </div>
            </div>
            
            <!-- Timer Settings Section -->
            <div class="pvp-admin-section">
                <h3>Timer Settings</h3>
                <form id="pvp-settings-form">
                    <label>Battle Duration (minutes)
                        <input type="number" name="battle_duration" id="battle_duration" min="1" max="60">
                    </label>
                    <label>Final Display Duration (minutes)
                        <input type="number" name="final_display" id="final_display" min="1" max="60">
                    </label>
                    <button type="submit" class="pvp-admin-btn save">💾 Save & Apply Now</button>
                </form>
            </div>
            
            <!-- Status Display -->
            <div class="pvp-admin-section">
                <h3>System Status</h3>
                <div id="pvp-status-display">Ready for commands...</div>
            </div>
        </div>
    </div>
</div>

<?php
// Don't use template.php for admin panel - output directly
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets_css/layout.css">
    <link rel="stylesheet" href="../farm_admin/admin-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="admin-panel-container">
        <?php echo ob_get_clean(); ?>
    </div>
    <script src="../farm_admin/admin-panel.js"></script>
    <script src="../farm_admin/achievements.js"></script>
</body>
</html>