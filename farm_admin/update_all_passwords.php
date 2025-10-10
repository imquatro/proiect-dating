<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

require_once '../includes/db.php';

// Check if user is admin
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate input
$errors = [];

if (empty($newPassword)) {
    $errors[] = 'New password is required';
} elseif (strlen($newPassword) < 6) {
    $errors[] = 'New password must be at least 6 characters long';
}

if (empty($confirmPassword)) {
    $errors[] = 'Confirm password is required';
}

if ($newPassword !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $errors]);
    exit;
}

try {
    $db->beginTransaction();
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update only auto-created user passwords
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE auto_account = 1");
    $result = $stmt->execute([$hashedPassword]);
    
    if ($result) {
        $affectedRows = $stmt->rowCount();
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Successfully updated passwords for {$affectedRows} auto-created users",
            'affected_users' => $affectedRows
        ]);
    } else {
        throw new Exception('Failed to update passwords');
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error updating passwords: ' . $e->getMessage()
    ]);
}
?>
