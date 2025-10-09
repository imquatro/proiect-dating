<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

require_once __DIR__ . '/includes/db.php';

try {
    $stmt = $db->prepare('SELECT loading_style FROM user_preferences WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $loadingStyle = $stmt->fetchColumn();

    if ($loadingStyle) {
        echo json_encode(['success' => true, 'loading_style' => $loadingStyle]);
    } else {
        // Return default style if no preference is saved
        echo json_encode(['success' => true, 'loading_style' => 'variant-1']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>

