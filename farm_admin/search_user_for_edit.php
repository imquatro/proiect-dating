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

$searchTerm = trim($_POST['search_term'] ?? '');

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit;
}

try {
    // Search for users by username or email
    $stmt = $db->prepare("
        SELECT id, username, email, admin_level, age, country, city, gender, auto_account, created_at
        FROM users 
        WHERE (username LIKE ? OR email LIKE ?)
        ORDER BY username ASC
        LIMIT 10
    ");
    
    $searchPattern = '%' . $searchTerm . '%';
    $stmt->execute([$searchPattern, $searchPattern]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo json_encode([
            'success' => true,
            'users' => [],
            'message' => 'No users found matching the search term'
        ]);
        exit;
    }
    
    // Format users data
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'admin_level' => $user['admin_level'],
            'grade_name' => getGradeName($user['admin_level']),
            'age' => $user['age'],
            'country' => $user['country'],
            'city' => $user['city'],
            'gender' => $user['gender'],
            'auto_account' => (bool)$user['auto_account'],
            'created_at' => $user['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formattedUsers
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching users: ' . $e->getMessage()
    ]);
}
?>
