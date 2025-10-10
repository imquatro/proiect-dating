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

try {
    // Get overall statistics
    $stmt = $db->query("
        SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN auto_account = 1 THEN 1 ELSE 0 END) as auto_users,
            SUM(CASE WHEN auto_account = 0 THEN 1 ELSE 0 END) as normal_users,
            SUM(CASE WHEN admin_level <= 2 THEN 1 ELSE 0 END) as total_admins
        FROM users
    ");
    $overallStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get grade distribution
    $stmt = $db->query("
        SELECT 
            admin_level,
            COUNT(*) as count
        FROM users 
        GROUP BY admin_level 
        ORDER BY admin_level ASC
    ");
    $gradeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format grade stats
    $gradeDistribution = [
        1 => 0, // SUPER_ADMIN
        2 => 0, // ADMIN
        3 => 0, // MODERATOR
        4 => 0, // HELPER
        5 => 0  // USER
    ];
    
    foreach ($gradeStats as $stat) {
        $gradeDistribution[$stat['admin_level']] = (int)$stat['count'];
    }
    
    // Get recent users (last 10)
    $stmt = $db->query("
        SELECT username, email, admin_level, auto_account, created_at
        FROM users 
        ORDER BY id DESC 
        LIMIT 10
    ");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format recent users
    $formattedRecentUsers = [];
    foreach ($recentUsers as $user) {
        $formattedRecentUsers[] = [
            'username' => $user['username'],
            'email' => $user['email'],
            'grade_name' => getGradeName($user['admin_level']),
            'admin_level' => $user['admin_level'],
            'auto_account' => $user['auto_account'],
            'created_at' => $user['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'statistics' => [
            'overall' => [
                'total_users' => (int)$overallStats['total_users'],
                'auto_users' => (int)$overallStats['auto_users'],
                'normal_users' => (int)$overallStats['normal_users'],
                'total_admins' => (int)$overallStats['total_admins']
            ],
            'grade_distribution' => $gradeDistribution,
            'recent_users' => $formattedRecentUsers
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching statistics: ' . $e->getMessage()
    ]);
}
?>
