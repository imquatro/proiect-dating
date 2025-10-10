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
        SELECT id, username, email, admin_level, is_banned, ban_reason, ban_end_date, banned_at
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
        $banStatus = 'Active';
        $banInfo = '';
        
        if ($user['is_banned']) {
            $banStatus = 'Banned';
            if ($user['ban_end_date']) {
                $banEndDate = new DateTime($user['ban_end_date']);
                $now = new DateTime();
                if ($banEndDate > $now) {
                    $banInfo = 'Until: ' . $banEndDate->format('Y-m-d H:i:s');
                } else {
                    $banInfo = 'Expired ban';
                }
            } else {
                $banInfo = 'Permanent ban';
            }
            if ($user['ban_reason']) {
                $banInfo .= ' - Reason: ' . $user['ban_reason'];
            }
        }
        
        $formattedUsers[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'admin_level' => $user['admin_level'],
            'grade_name' => getGradeName($user['admin_level']),
            'ban_status' => $banStatus,
            'ban_info' => $banInfo,
            'is_banned' => (bool)$user['is_banned']
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
