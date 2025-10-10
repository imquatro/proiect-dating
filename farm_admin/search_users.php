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

$searchTerm = trim($_POST['search_term'] ?? '');

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit;
}

try {
    // Search for users by username or email
    $stmt = $db->prepare("
        SELECT id, username, email, admin_level, auto_account, created_at 
        FROM users 
        WHERE username LIKE ? OR email LIKE ? 
        ORDER BY admin_level ASC, username ASC 
        LIMIT 20
    ");
    $searchPattern = '%' . $searchTerm . '%';
    $stmt->execute([$searchPattern, $searchPattern]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo json_encode(['success' => true, 'message' => 'No users found', 'users' => []]);
        exit;
    }
    
    // Format users data
    $gradeNames = [
        1 => 'SUPER_ADMIN',
        2 => 'ADMIN',
        3 => 'MODERATOR', 
        4 => 'HELPER',
        5 => 'USER'
    ];
    
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'admin_level' => $user['admin_level'],
            'grade_name' => $gradeNames[$user['admin_level']],
            'auto_account' => $user['auto_account'],
            'created_at' => $user['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Users found',
        'users' => $formattedUsers
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching users: ' . $e->getMessage()
    ]);
}
?>
