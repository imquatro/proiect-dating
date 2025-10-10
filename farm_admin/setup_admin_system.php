<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';

// Check if user is admin (we'll use is_admin for now since admin_level doesn't exist yet)
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$isAdmin = $stmt->fetchColumn();

if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Admin only']);
    exit;
}

header('Content-Type: application/json');

try {
    $db->beginTransaction();
    
    // Check if admin_level column already exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'admin_level'");
    $adminLevelExists = $stmt->fetch();
    
    if (!$adminLevelExists) {
        // Add admin_level column
        $db->exec("ALTER TABLE users ADD COLUMN admin_level TINYINT(1) NOT NULL DEFAULT 5 
                   COMMENT 'Admin level: 1=SUPER_ADMIN, 2=ADMIN, 3=MODERATOR, 4=HELPER, 5=USER'");
    }
    
    // Check if auto_account column already exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'auto_account'");
    $autoAccountExists = $stmt->fetch();
    
    if (!$autoAccountExists) {
        // Add auto_account column
        $db->exec("ALTER TABLE users ADD COLUMN auto_account TINYINT(1) NOT NULL DEFAULT 0 
                   COMMENT '1 = cont creat din admin panel, 0 = cont creat prin înregistrare normală'");
    }
    
    // Check if is_banned column already exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
    $isBannedExists = $stmt->fetch();
    
    if (!$isBannedExists) {
        // Add ban management columns
        $db->exec("ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0 
                   COMMENT '1 = user is banned, 0 = user is active'");
        $db->exec("ALTER TABLE users ADD COLUMN ban_reason TEXT NULL 
                   COMMENT 'Reason for ban'");
        $db->exec("ALTER TABLE users ADD COLUMN ban_end_date DATETIME NULL 
                   COMMENT 'When ban expires (NULL for permanent)'");
        $db->exec("ALTER TABLE users ADD COLUMN banned_by VARCHAR(50) NULL 
                   COMMENT 'Username of admin who banned the user'");
        $db->exec("ALTER TABLE users ADD COLUMN banned_at DATETIME NULL 
                   COMMENT 'When the user was banned'");
    }
    
    // Update existing users to have proper admin levels
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE admin_level = 5 AND is_admin = 1");
    $needsUpdate = $stmt->fetchColumn() > 0;
    
    if ($needsUpdate) {
        // Set current admins (is_admin = 1) to admin_level = 2 (ADMIN)
        $stmt = $db->prepare("UPDATE users SET admin_level = 2 WHERE is_admin = 1");
        $stmt->execute();
        
        // Set the first user (quatro) to SUPER_ADMIN (admin_level = 1)
        $stmt = $db->prepare("UPDATE users SET admin_level = 1 WHERE id = 1");
        $stmt->execute();
        
        // Set all other users to USER level (admin_level = 5)
        $stmt = $db->prepare("UPDATE users SET admin_level = 5 WHERE is_admin = 0");
        $stmt->execute();
        
        // Set all existing users to normal accounts (auto_account = 0)
        $stmt = $db->prepare("UPDATE users SET auto_account = 0");
        $stmt->execute();
    }
    
    // Create admin_activity_logs table if it doesn't exist
    $stmt = $db->query("SHOW TABLES LIKE 'admin_activity_logs'");
    $logsTableExists = $stmt->fetch();
    
    if (!$logsTableExists) {
        $db->exec("CREATE TABLE admin_activity_logs (
            id INT(11) NOT NULL AUTO_INCREMENT,
            admin_id INT(11) NOT NULL,
            action_type VARCHAR(50) NOT NULL,
            target_user_id INT(11) NULL,
            details TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_admin_id (admin_id),
            INDEX idx_action_type (action_type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }
    
    // Add indexes for better performance (ignore errors if they already exist)
    try {
        $db->exec("CREATE INDEX idx_users_admin_level ON users(admin_level)");
    } catch (PDOException $e) {
        // Index might already exist, ignore error
    }
    
    try {
        $db->exec("CREATE INDEX idx_users_auto_account ON users(auto_account)");
    } catch (PDOException $e) {
        // Index might already exist, ignore error
    }
    
    try {
        $db->exec("CREATE INDEX idx_users_is_banned ON users(is_banned)");
    } catch (PDOException $e) {
        // Index might already exist, ignore error
    }
    
    $db->commit();
    
    // Get updated statistics
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE admin_level = 1");
    $superAdmins = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE admin_level = 2");
    $admins = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE auto_account = 1");
    $autoUsers = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin system setup completed successfully!',
        'statistics' => [
            'total_users' => $totalUsers,
            'super_admins' => $superAdmins,
            'admins' => $admins,
            'auto_users' => $autoUsers
        ]
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
