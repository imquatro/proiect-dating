<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';

// Check if user is admin
$stmt = $db->prepare('SELECT admin_level FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$currentAdminLevel = $stmt->fetchColumn();

if (!$currentAdminLevel || $currentAdminLevel > 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied - Admin level required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$targetUserId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$newAdminLevel = isset($_POST['new_admin_level']) ? intval($_POST['new_admin_level']) : 0;
$reason = trim($_POST['reason'] ?? '');

// Validate input
$errors = [];

if ($targetUserId < 1) {
    $errors[] = 'Invalid user ID';
}

if ($newAdminLevel < 1 || $newAdminLevel > 5) {
    $errors[] = 'Invalid admin level';
}

if (empty($reason)) {
    $errors[] = 'Reason is required';
}

// SUPER_ADMIN can change anyone, ADMIN can only change levels 3-5
if ($currentAdminLevel == 2) {
    if ($newAdminLevel < 3) {
        $errors[] = 'ADMIN level cannot promote users to ADMIN or SUPER_ADMIN';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $errors]);
    exit;
}

try {
    $db->beginTransaction();
    
    // Get target user info
    $stmt = $db->prepare("SELECT id, username, email, admin_level FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$targetUser) {
        throw new Exception('User not found');
    }
    
    $oldLevel = $targetUser['admin_level'];
    
    // Check if trying to change to same level
    if ($oldLevel == $newAdminLevel) {
        echo json_encode(['success' => false, 'message' => 'User already has this admin level']);
        exit;
    }
    
    // Update user's admin level
    $stmt = $db->prepare("UPDATE users SET admin_level = ? WHERE id = ?");
    $result = $stmt->execute([$newAdminLevel, $targetUserId]);
    
    if ($result) {
        // Get current admin info
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $currentAdminName = $stmt->fetchColumn();
        
        // Create activity log entry
        $stmt = $db->prepare("
            INSERT INTO admin_activity_logs 
            (admin_id, admin_username, action, target_user_id, target_username, old_value, new_value, reason, created_at) 
            VALUES (?, ?, 'grade_change', ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $currentAdminName,
            $targetUserId,
            $targetUser['username'],
            $oldLevel,
            $newAdminLevel,
            $reason
        ]);
        
        $db->commit();
        
        // Get grade names
        $gradeNames = [
            1 => 'SUPER_ADMIN',
            2 => 'ADMIN', 
            3 => 'MODERATOR',
            4 => 'HELPER',
            5 => 'USER'
        ];
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully changed {$targetUser['username']}'s grade from {$gradeNames[$oldLevel]} to {$gradeNames[$newAdminLevel]}",
            'user' => [
                'id' => $targetUser['id'],
                'username' => $targetUser['username'],
                'email' => $targetUser['email'],
                'old_level' => $oldLevel,
                'new_level' => $newAdminLevel,
                'old_grade' => $gradeNames[$oldLevel],
                'new_grade' => $gradeNames[$newAdminLevel]
            ]
        ]);
        
    } else {
        throw new Exception('Failed to update user grade');
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error changing user grade: ' . $e->getMessage()
    ]);
}
?>
