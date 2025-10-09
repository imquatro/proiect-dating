<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/includes/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$loadingStyle = $input['loading_style'] ?? 'variant-1';

// Validate variant
$validVariants = [
    'variant-1', 'variant-2', 'variant-3', 'variant-4', 'variant-5',
    'variant-6', 'variant-7', 'variant-8', 'variant-9', 'variant-10',
    'variant-11', 'variant-12', 'variant-13', 'variant-14', 'variant-15',
    'variant-16', 'variant-17', 'variant-18', 'variant-19', 'variant-20',
    'variant-21', 'variant-22', 'variant-23', 'variant-24', 'variant-25'
];
if (!in_array($loadingStyle, $validVariants)) {
    echo json_encode(['success' => false, 'message' => 'Invalid loading style']);
    exit;
}

try {
    // Check if user preference exists
    $stmt = $db->prepare('SELECT id FROM user_preferences WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Update existing preference
        $stmt = $db->prepare('UPDATE user_preferences SET loading_style = ? WHERE user_id = ?');
        $stmt->execute([$loadingStyle, $_SESSION['user_id']]);
    } else {
        // Insert new preference
        $stmt = $db->prepare('INSERT INTO user_preferences (user_id, loading_style) VALUES (?, ?)');
        $stmt->execute([$_SESSION['user_id'], $loadingStyle]);
    }

    echo json_encode(['success' => true, 'message' => 'Loading style saved successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

