<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';
require_once '../includes/admin_permissions.php';

// Check if user is SUPER_ADMIN
requireCurrentUserPermission('manage_admins');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId = intval($_POST['user_id'] ?? 0);
$action = $_POST['ban_action'] ?? '';
$reason = trim($_POST['ban_reason'] ?? '');
$duration = intval($_POST['ban_duration'] ?? 0);

if (!$userId || !$action || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (!in_array($action, ['ban', 'unban'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $db->beginTransaction();
    
    // Check if target user exists
    $stmt = $db->prepare("SELECT id, username, admin_level FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$targetUser) {
        throw new Exception('User not found');
    }
    
    // Prevent SUPER_ADMIN from banning other SUPER_ADMINs
    $currentUserAdminLevel = $_SESSION['admin_level'] ?? 5;
    if ($targetUser['admin_level'] == 1 && $currentUserAdminLevel > 1) {
        throw new Exception('Cannot ban SUPER_ADMIN accounts');
    }
    
    // Get current admin info for logging
    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentAdmin = $stmt->fetchColumn();
    
    if ($action === 'ban') {
        // Calculate ban end date
        $banEndDate = null;
        if ($duration > 0) {
            $banEndDate = date('Y-m-d H:i:s', strtotime("+$duration days"));
        }
        
        // Update user ban status
        $stmt = $db->prepare("
            UPDATE users 
            SET is_banned = 1, ban_reason = ?, ban_end_date = ?, banned_by = ?, banned_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$reason, $banEndDate, $currentAdmin, $userId]);
        
        // Log admin action
        $stmt = $db->prepare("
            INSERT INTO admin_activity_logs (admin_id, action_type, target_user_id, details, created_at) 
            VALUES (?, 'ban_user', ?, ?, NOW())
        ");
        $details = json_encode([
            'target_username' => $targetUser['username'],
            'reason' => $reason,
            'duration_days' => $duration,
            'ban_end_date' => $banEndDate
        ]);
        $stmt->execute([$_SESSION['user_id'], $userId, $details]);
        
        $message = "User '{$targetUser['username']}' has been banned";
        if ($duration > 0) {
            $message .= " for $duration days";
        } else {
            $message .= " permanently";
        }
        
    } else { // unban
        // Update user ban status
        $stmt = $db->prepare("
            UPDATE users 
            SET is_banned = 0, ban_reason = NULL, ban_end_date = NULL, banned_by = NULL, banned_at = NULL
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        // Log admin action
        $stmt = $db->prepare("
            INSERT INTO admin_activity_logs (admin_id, action_type, target_user_id, details, created_at) 
            VALUES (?, 'unban_user', ?, ?, NOW())
        ");
        $details = json_encode([
            'target_username' => $targetUser['username'],
            'reason' => $reason
        ]);
        $stmt->execute([$_SESSION['user_id'], $userId, $details]);
        
        $message = "User '{$targetUser['username']}' has been unbanned";
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
