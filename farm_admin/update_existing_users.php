<?php
// Script pentru actualizarea conturilor existente cu câmpul auto_account
// ATENȚIE: Rulează acest script DOAR o dată după ce ai adăugat câmpul auto_account

session_start();
if (!isset($_SESSION['user_id'])) {
    die('Access denied - please login as admin');
}

require_once '../includes/db.php';

// Check if user is admin
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    die('Access denied - admin only');
}

// Check if auto_account field exists
$stmt = $db->query("SHOW COLUMNS FROM users LIKE 'auto_account'");
if ($stmt->rowCount() == 0) {
    die('auto_account field does not exist. Please run the SQL script first.');
}

echo "<h1>Update Existing Users with auto_account Field</h1>";

try {
    $db->beginTransaction();
    
    // Set all existing users to normal accounts (auto_account = 0)
    $stmt = $db->prepare("UPDATE users SET auto_account = 0 WHERE auto_account IS NULL");
    $result = $stmt->execute();
    
    if ($result) {
        $affectedRows = $stmt->rowCount();
        $db->commit();
        
        echo "<p style='color: green;'>✅ Successfully updated {$affectedRows} existing users with auto_account = 0 (normal accounts)</p>";
        
        // Show statistics
        $stmt = $db->query("SELECT 
            COUNT(*) as total_users,
            SUM(CASE WHEN auto_account = 1 THEN 1 ELSE 0 END) as auto_users,
            SUM(CASE WHEN auto_account = 0 THEN 1 ELSE 0 END) as normal_users
            FROM users");
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h2>Current Statistics:</h2>";
        echo "<ul>";
        echo "<li><strong>Total Users:</strong> {$stats['total_users']}</li>";
        echo "<li><strong>Auto-Created Users:</strong> {$stats['auto_users']}</li>";
        echo "<li><strong>Normal Users:</strong> {$stats['normal_users']}</li>";
        echo "</ul>";
        
        echo "<h2>✅ Setup Complete!</h2>";
        echo "<p>The auto_account system is now fully functional:</p>";
        echo "<ul>";
        echo "<li>✅ Existing users marked as normal accounts (auto_account = 0)</li>";
        echo "<li>✅ New auto-created users will have auto_account = 1</li>";
        echo "<li>✅ New manual-created users will have auto_account = 0</li>";
        echo "<li>✅ Password updates will only affect auto-created accounts</li>";
        echo "</ul>";
        
    } else {
        throw new Exception('Failed to update users');
    }
    
} catch (Exception $e) {
    $db->rollBack();
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
