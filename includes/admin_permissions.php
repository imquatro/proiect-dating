<?php
// Helper functions for admin permission checking

/**
 * Get admin level for a user
 */
function getAdminLevel($userId) {
    global $db;
    
    // Check if admin_level column exists
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'admin_level'");
    $adminLevelExists = $stmt->fetch();
    
    if ($adminLevelExists) {
        $stmt = $db->prepare('SELECT admin_level FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() ?: 5; // Default to USER level
    } else {
        // Fallback to is_admin column
        $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $isAdmin = $stmt->fetchColumn();
        return $isAdmin ? 2 : 5; // ADMIN if is_admin = 1, USER otherwise
    }
}

/**
 * Check if user is SUPER_ADMIN (level 1)
 */
function isSuperAdmin($userId) {
    return getAdminLevel($userId) <= 1;
}

/**
 * Check if user is ADMIN or higher (level 2 or lower)
 */
function isAdmin($userId) {
    return getAdminLevel($userId) <= 2;
}

/**
 * Check if user is MODERATOR or higher (level 3 or lower)
 */
function isModerator($userId) {
    return getAdminLevel($userId) <= 3;
}

/**
 * Check if user is HELPER or higher (level 4 or lower)
 */
function isHelper($userId) {
    return getAdminLevel($userId) <= 4;
}

/**
 * Get grade name from level
 */
function getGradeName($level) {
    $grades = [
        1 => 'SUPER_ADMIN',
        2 => 'ADMIN',
        3 => 'MODERATOR',
        4 => 'HELPER',
        5 => 'USER'
    ];
    return $grades[$level] ?? 'UNKNOWN';
}

/**
 * Check if user can perform specific action
 */
function canPerformAction($userId, $action) {
    $level = getAdminLevel($userId);
    
    $permissions = [
        'manage_admins' => $level <= 1,           // SUPER_ADMIN only
        'change_grades' => $level <= 2,           // ADMIN and above
        'create_test_accounts' => $level <= 2,    // ADMIN and above
        'manage_items' => $level <= 2,            // ADMIN and above
        'manage_achievements' => $level <= 2,     // ADMIN and above
        'update_passwords' => $level <= 2,        // ADMIN and above
        'manage_users' => $level <= 3,            // MODERATOR and above
        'view_statistics' => $level <= 2,         // ADMIN and above
        'view_logs' => $level <= 1,               // SUPER_ADMIN only
        'help_users' => $level <= 4,              // HELPER and above
    ];
    
    return $permissions[$action] ?? false;
}

/**
 * Require specific permission level
 */
function requirePermission($userId, $action) {
    if (!canPerformAction($userId, $action)) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied - insufficient permissions'
        ]);
        exit;
    }
}

/**
 * Log admin activity
 */
function logAdminActivity($adminId, $action, $targetUserId = null, $oldValue = null, $newValue = null, $reason = null) {
    global $db;
    
    try {
        // Get admin username
        $stmt = $db->prepare('SELECT username FROM users WHERE id = ?');
        $stmt->execute([$adminId]);
        $adminUsername = $stmt->fetchColumn();
        
        // Get target username if applicable
        $targetUsername = null;
        if ($targetUserId) {
            $stmt = $db->prepare('SELECT username FROM users WHERE id = ?');
            $stmt->execute([$targetUserId]);
            $targetUsername = $stmt->fetchColumn();
        }
        
        // Insert log entry
        $stmt = $db->prepare("
            INSERT INTO admin_activity_logs 
            (admin_id, admin_username, action, target_user_id, target_username, old_value, new_value, reason, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $adminId,
            $adminUsername,
            $action,
            $targetUserId,
            $targetUsername,
            $oldValue,
            $newValue,
            $reason,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to log admin activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user's current session admin level
 */
function getCurrentUserAdminLevel() {
    if (!isset($_SESSION['user_id'])) {
        return 5; // USER level
    }
    return getAdminLevel($_SESSION['user_id']);
}

/**
 * Check if current user can perform action
 */
function currentUserCan($action) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    return canPerformAction($_SESSION['user_id'], $action);
}

/**
 * Require current user to have permission
 */
function requireCurrentUserPermission($action) {
    if (!currentUserCan($action)) {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Access denied - insufficient permissions'
        ]);
        exit;
    }
}
?>
