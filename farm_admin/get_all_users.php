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
$gradeFilter = $_POST['grade_filter'] ?? '';
$typeFilter = $_POST['type_filter'] ?? '';
$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if (!empty($searchTerm)) {
        $conditions[] = "(username LIKE ? OR email LIKE ? OR id = ?)";
        $searchPattern = '%' . $searchTerm . '%';
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        if (is_numeric($searchTerm)) {
            $params[] = intval($searchTerm);
        } else {
            $params[] = 0;
        }
    }
    
    if (!empty($gradeFilter)) {
        $conditions[] = "admin_level = ?";
        $params[] = intval($gradeFilter);
    }
    
    if ($typeFilter !== '') {
        $conditions[] = "auto_account = ?";
        $params[] = intval($typeFilter);
    }
    
    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM users $whereClause";
    $stmt = $db->prepare($countQuery);
    $stmt->execute($params);
    $totalUsers = $stmt->fetchColumn();
    $totalPages = ceil($totalUsers / $limit);
    
    // Get users with pagination
    $usersQuery = "
        SELECT id, username, email, admin_level, auto_account, age, country, city, gender, created_at
        FROM users 
        $whereClause
        ORDER BY admin_level ASC, id DESC 
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($usersQuery);
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format users data
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'admin_level' => $user['admin_level'],
            'grade_name' => getGradeName($user['admin_level']),
            'auto_account' => $user['auto_account'],
            'age' => $user['age'],
            'country' => $user['country'],
            'city' => $user['city'],
            'gender' => $user['gender'],
            'created_at' => $user['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'users' => $formattedUsers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_users' => $totalUsers,
            'per_page' => $limit
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching users: ' . $e->getMessage()
    ]);
}
?>
