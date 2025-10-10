<?php
// Script pentru afișarea statisticilor conturilor
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

echo "<h1>User Account Statistics</h1>";

try {
    // Get overall statistics
    $stmt = $db->query("SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN auto_account = 1 THEN 1 ELSE 0 END) as auto_users,
        SUM(CASE WHEN auto_account = 0 THEN 1 ELSE 0 END) as normal_users,
        SUM(CASE WHEN auto_account IS NULL THEN 1 ELSE 0 END) as null_users
        FROM users");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>📊 Overall Statistics:</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Account Type</th><th>Count</th><th>Percentage</th>";
    echo "</tr>";
    
    $total = $stats['total_users'];
    
    echo "<tr>";
    echo "<td><strong>Total Users</strong></td>";
    echo "<td>{$total}</td>";
    echo "<td>100%</td>";
    echo "</tr>";
    
    echo "<tr style='background: #e8f5e8;'>";
    echo "<td>Auto-Created Users (Admin Panel)</td>";
    echo "<td>{$stats['auto_users']}</td>";
    echo "<td>" . round(($stats['auto_users'] / $total) * 100, 1) . "%</td>";
    echo "</tr>";
    
    echo "<tr style='background: #e8f0ff;'>";
    echo "<td>Normal Users (Registration)</td>";
    echo "<td>{$stats['normal_users']}</td>";
    echo "<td>" . round(($stats['normal_users'] / $total) * 100, 1) . "%</td>";
    echo "</tr>";
    
    if ($stats['null_users'] > 0) {
        echo "<tr style='background: #ffe8e8;'>";
        echo "<td>⚠️ Users with NULL auto_account</td>";
        echo "<td>{$stats['null_users']}</td>";
        echo "<td>" . round(($stats['null_users'] / $total) * 100, 1) . "%</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Get recent auto-created users
    $stmt = $db->query("SELECT username, email, created_at FROM users WHERE auto_account = 1 ORDER BY id DESC LIMIT 10");
    $recentAuto = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($recentAuto)) {
        echo "<h2>🤖 Recent Auto-Created Users:</h2>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Username</th><th>Email</th><th>Created</th>";
        echo "</tr>";
        
        foreach ($recentAuto as $user) {
            echo "<tr>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>" . ($user['created_at'] ?? 'Unknown') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    // Get recent normal users
    $stmt = $db->query("SELECT username, email, created_at FROM users WHERE auto_account = 0 ORDER BY id DESC LIMIT 10");
    $recentNormal = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($recentNormal)) {
        echo "<h2>👤 Recent Normal Users:</h2>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>Username</th><th>Email</th><th>Created</th>";
        echo "</tr>";
        
        foreach ($recentNormal as $user) {
            echo "<tr>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>" . ($user['created_at'] ?? 'Unknown') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<h2>🔧 System Status:</h2>";
    echo "<ul>";
    echo "<li>✅ <strong>auto_account field:</strong> " . ($stats['null_users'] == 0 ? "All users have proper values" : "⚠️ Some users need updating") . "</li>";
    echo "<li>✅ <strong>Auto-creation system:</strong> " . ($stats['auto_users'] > 0 ? "Working (created {$stats['auto_users']} accounts)" : "Not used yet") . "</li>";
    echo "<li>✅ <strong>Normal registration:</strong> " . ($stats['normal_users'] > 0 ? "Working (created {$stats['normal_users']} accounts)" : "No normal registrations") . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
