<?php
/**
 * Enroll Existing Users in PVP
 * 
 * Acceseaza: http://localhost/1/enroll_existing_users_pvp.php
 */

echo "<h1>Enroll Existing Users in PVP</h1>";

require_once 'includes/db.php';
require_once 'includes/pvp_helpers.php';

try {
    // Enroll all existing users in Bronze league
    $result = $db->exec("
        INSERT IGNORE INTO user_league_status (user_id, league_id) 
        SELECT id, 1 FROM users 
        WHERE id NOT IN (SELECT user_id FROM user_league_status)
    ");
    
    echo "<h2>✅ Enrollment Complete!</h2>";
    echo "<p>All existing users have been enrolled in Bronze league.</p>";
    
    // Show statistics
    $stmt = $db->query("SELECT COUNT(*) as total FROM user_league_status WHERE league_id = 1");
    $bronzeCount = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetchColumn();
    
    echo "<h3>📊 Statistics:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Users:</strong> $totalUsers</li>";
    echo "<li><strong>Users in Bronze League:</strong> $bronzeCount</li>";
    echo "<li><strong>Ready for PVP:</strong> " . ($bronzeCount >= 32 ? "✅ YES ($bronzeCount/32)" : "❌ NO ($bronzeCount/32)") . "</li>";
    echo "</ul>";
    
    if ($bronzeCount >= 32) {
        echo "<h3>🚀 Ready to Start PVP Battle!</h3>";
        echo "<p><a href='test_cron.php'>→ Start PVP Battle Now</a></p>";
    } else {
        echo "<h3>⏳ Need More Users</h3>";
        echo "<p>Create " . (32 - $bronzeCount) . " more users to start PVP battle.</p>";
        echo "<p><a href='farm_admin/panel.php'>→ Go to Admin Panel</a></p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='pvp_battles.php'>→ Go to PVP Battles</a></p>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
