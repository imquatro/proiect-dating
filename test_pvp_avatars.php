<?php
/**
 * Test PVP Avatars and VS Display
 * 
 * Acceseaza: http://localhost/1/test_pvp_avatars.php
 */

echo "<h1>Test PVP Avatars & VS Display</h1>";

require_once 'includes/db.php';
require_once 'includes/pvp_helpers.php';

try {
    // Check if there's an active battle
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeBattle) {
        echo "<h2>❌ No Active Battle Found</h2>";
        echo "<p>Start a battle first: <a href='test_cron.php'>→ Start PVP Battle</a></p>";
        exit;
    }
    
    echo "<h2>✅ Active Battle Found</h2>";
    echo "<p><strong>Battle ID:</strong> {$activeBattle['id']}</p>";
    echo "<p><strong>League ID:</strong> {$activeBattle['league_id']}</p>";
    echo "<p><strong>Current Round:</strong> {$activeBattle['current_round']}</p>";
    echo "<p><strong>Status:</strong> {$activeBattle['status']}</p>";
    
    // Get matches for current round
    $stmt = $db->prepare("
        SELECT m.*, 
               u1.id as u1_id,
               u1.username as user1_name, 
               CASE 
                   WHEN u1.gallery IS NOT NULL AND u1.gallery != '' THEN CONCAT('uploads/', u1.id, '/', TRIM(SUBSTRING_INDEX(u1.gallery, ',', 1)))
                   ELSE 'default-avatar.png'
               END as user1_photo,
               u1.vip as user1_vip,
               u2.id as u2_id,
               u2.username as user2_name, 
               CASE 
                   WHEN u2.gallery IS NOT NULL AND u2.gallery != '' THEN CONCAT('uploads/', u2.id, '/', TRIM(SUBSTRING_INDEX(u2.gallery, ',', 1)))
                   ELSE 'default-avatar.png'
               END as user2_photo,
               u2.vip as user2_vip
        FROM pvp_matches m
        LEFT JOIN users u1 ON m.user1_id = u1.id
        LEFT JOIN users u2 ON m.user2_id = u2.id
        WHERE m.battle_id = ? AND m.round_number = ?
        ORDER BY m.id
        LIMIT 5
    ");
    $stmt->execute([$activeBattle['id'], $activeBattle['current_round']]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>🎮 Sample Matches (Round {$activeBattle['current_round']}):</h3>";
    
    if (empty($matches)) {
        echo "<p>❌ No matches found for current round.</p>";
    } else {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
        
        foreach ($matches as $match) {
            echo "<div style='border: 2px solid #ddd; padding: 15px; border-radius: 10px; background: #f9f9f9;'>";
            echo "<h4>Match #{$match['id']}</h4>";
            
            // Player 1
            echo "<div style='display: flex; align-items: center; margin: 10px 0;'>";
            echo "<img src='{$match['user1_photo']}' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;' alt='{$match['user1_name']}'>";
            echo "<div>";
            echo "<strong>{$match['user1_name']}</strong><br>";
            echo "Score: {$match['user1_score']}<br>";
            echo "VIP: " . ($match['user1_vip'] ? 'Yes' : 'No');
            echo "</div>";
            echo "</div>";
            
            echo "<div style='text-align: center; font-size: 18px; font-weight: bold; color: #ff4444;'>VS</div>";
            
            // Player 2
            echo "<div style='display: flex; align-items: center; margin: 10px 0;'>";
            echo "<img src='{$match['user2_photo']}' style='width: 50px; height: 50px; border-radius: 50%; margin-right: 10px;' alt='{$match['user2_name']}'>";
            echo "<div>";
            echo "<strong>{$match['user2_name']}</strong><br>";
            echo "Score: {$match['user2_score']}<br>";
            echo "VIP: " . ($match['user2_vip'] ? 'Yes' : 'No');
            echo "</div>";
            echo "</div>";
            
            echo "<div style='margin-top: 10px; padding: 5px; background: #e0e0e0; border-radius: 5px;'>";
            echo "<strong>Status:</strong> " . ($match['completed'] ? 'Completed' : 'Active');
            if ($match['winner_id']) {
                echo " | <strong>Winner:</strong> " . ($match['winner_id'] == $match['user1_id'] ? $match['user1_name'] : $match['user2_name']);
            }
            echo "</div>";
            
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    // Check total matches
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_matches WHERE battle_id = ?");
    $stmt->execute([$activeBattle['id']]);
    $totalMatches = $stmt->fetchColumn();
    
    echo "<h3>📊 Battle Statistics:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Matches:</strong> $totalMatches</li>";
    echo "<li><strong>Current Round:</strong> {$activeBattle['current_round']}</li>";
    echo "<li><strong>Battle Status:</strong> {$activeBattle['status']}</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Links:</h3>";
    echo "<p><a href='pvp_battles.php'>→ Go to PVP Battles (Visual)</a></p>";
    echo "<p><a href='test_cron.php'>→ Run CRON (Advance Round)</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
