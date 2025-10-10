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
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = intval($_POST['age'] ?? 0);
$country = trim($_POST['country'] ?? '');
$city = trim($_POST['city'] ?? '');
$gender = $_POST['gender'] ?? '';
$adminLevel = intval($_POST['admin_level'] ?? 5);
$autoAccount = intval($_POST['auto_account'] ?? 0);

// Validation
if (!$userId || !$username || !$email || !$age || !$country || !$city || !$gender) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!in_array($gender, ['masculin', 'feminin'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid gender']);
    exit;
}

if ($adminLevel < 1 || $adminLevel > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid admin level']);
    exit;
}

if ($age < 18 || $age > 99) {
    echo json_encode(['success' => false, 'message' => 'Age must be between 18 and 99']);
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
    
    // Prevent SUPER_ADMIN from changing other SUPER_ADMINs to lower levels
    $currentUserAdminLevel = $_SESSION['admin_level'] ?? 5;
    if ($targetUser['admin_level'] == 1 && $adminLevel > 1 && $currentUserAdminLevel > 1) {
        throw new Exception('Cannot downgrade SUPER_ADMIN accounts');
    }
    
    // Check if username is already taken by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetchColumn()) {
        throw new Exception('Username is already taken');
    }
    
    // Check if email is already taken by another user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->fetchColumn()) {
        throw new Exception('Email is already taken');
    }
    
    // Update user
    $stmt = $db->prepare("
        UPDATE users 
        SET username = ?, email = ?, age = ?, country = ?, city = ?, gender = ?, admin_level = ?, auto_account = ?
        WHERE id = ?
    ");
    $stmt->execute([$username, $email, $age, $country, $city, $gender, $adminLevel, $autoAccount, $userId]);
    
    // Log admin action
    $stmt = $db->prepare("
        INSERT INTO admin_activity_logs (admin_id, action_type, target_user_id, details, created_at) 
        VALUES (?, 'edit_user', ?, ?, NOW())
    ");
    $details = json_encode([
        'target_username' => $targetUser['username'],
        'new_username' => $username,
        'changes' => [
            'username' => $targetUser['username'] !== $username ? ['old' => $targetUser['username'], 'new' => $username] : null,
            'email' => $email,
            'age' => $age,
            'country' => $country,
            'city' => $city,
            'gender' => $gender,
            'admin_level' => $adminLevel,
            'auto_account' => $autoAccount
        ]
    ]);
    $stmt->execute([$_SESSION['user_id'], $userId, $details]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "User '{$username}' has been updated successfully"
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
