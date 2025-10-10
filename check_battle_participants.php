<?php
/**
 * Check Battle Participants
 * 
 * Acceseaza: http://localhost/1/check_battle_participants.php
 */

echo "<h1>Check Battle Participants</h1>";

require_once 'includes/db.php';
require_once 'includes/pvp_helpers.php';

try {
    // Check active battle
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeBattle) {
        echo "<h2>❌ No Active Battle Found</h2>";
        echo "<p><a href='test_cron.php'>→ Start PVP Battle</a></p>";
        exit;
    }
    
    echo "<h2>✅ Active Battle Found</h2>";
    echo "<p><strong>Battle ID:</strong> {$activeBattle['id']}</p>";
    echo "<p><strong>League ID:</strong> {$activeBattle['league_id']}</p>";
    echo "<p><strong>Current Round:</strong> {$activeBattle['current_round']}</p>";
    
    // Get all users in Bronze league
    $stmt = $db->prepare("
        SELECT COUNT(*) as total_bronze_users
        FROM users u
        JOIN user_league_status uls ON u.id = uls.user_id
        WHERE uls.league_id = 1 AND u.is_active = 1
    ");
    $stmt->execute();
    $totalBronzeUsers = $stmt->fetchColumn();
    
    // Get battle participants
    $stmt = $db->prepare("
        SELECT p.*, u.username, u.gallery, u.vip, u.id as user_db_id
        FROM pvp_participants p
        JOIN users u ON p.user_id = u.id
        WHERE p.battle_id = ?
        ORDER BY p.popularity_score DESC, u.username
    ");
    $stmt->execute([$activeBattle['id']]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 Statistics:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Users in Bronze League:</strong> $totalBronzeUsers</li>";
    echo "<li><strong>Selected for Battle:</strong> " . count($participants) . " (random selection)</li>";
    echo "<li><strong>Not Selected:</strong> " . ($totalBronzeUsers - count($participants)) . " users</li>";
    echo "</ul>";
    
    echo "<h3>🎮 Battle Participants (Top 32 Random):</h3>";
    
    if (empty($participants)) {
        echo "<p>❌ No participants found.</p>";
    } else {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0;'>";
        
        foreach ($participants as $participant) {
            // Build avatar path
            $avatar = 'default-avatar.png';
            if (!empty($participant['gallery'])) {
                $gal = explode(',', $participant['gallery']);
                $avatar = 'uploads/' . $participant['user_db_id'] . '/' . trim($gal[0]);
            }
            $vipClass = $participant['vip'] ? 'gold-shimmer' : '';
            
            echo "<div style='border: 2px solid #ddd; padding: 10px; border-radius: 8px; background: #f9f9f9;'>";
            echo "<div style='display: flex; align-items: center; margin-bottom: 8px;'>";
            echo "<img src='$avatar' style='width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;' alt='{$participant['username']}'>";
            echo "<div>";
            echo "<strong class='$vipClass'>{$participant['username']}</strong><br>";
            echo "Score: {$participant['popularity_score']}";
            if ($participant['vip']) echo " <span style='color: gold;'>👑</span>";
            echo "</div>";
            echo "</div>";
            
            if ($participant['eliminated_in_round']) {
                echo "<div style='color: #ff4444; font-size: 12px;'>Eliminated in Round {$participant['eliminated_in_round']}</div>";
            } elseif ($participant['final_position']) {
                echo "<div style='color: #4caf50; font-size: 12px;'>Final Position: {$participant['final_position']}</div>";
            } else {
                echo "<div style='color: #2196f3; font-size: 12px;'>Still Active</div>";
            }
            
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    // Show users NOT selected
    $stmt = $db->prepare("
        SELECT u.id, u.username, u.gallery, u.vip
        FROM users u
        JOIN user_league_status uls ON u.id = uls.user_id
        WHERE uls.league_id = 1 AND u.is_active = 1
        AND u.id NOT IN (SELECT user_id FROM pvp_participants WHERE battle_id = ?)
        ORDER BY u.username
        LIMIT 10
    ");
    $stmt->execute([$activeBattle['id']]);
    $notSelected = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($notSelected)) {
        echo "<h3>👥 Users NOT Selected (Sample):</h3>";
        echo "<p style='color: #666;'>These users are in Bronze league but were not randomly selected for this battle:</p>";
        echo "<div style='display: flex; flex-wrap: wrap; gap: 10px; margin: 10px 0;'>";
        
        foreach ($notSelected as $user) {
            // Build avatar path
            $avatar = 'default-avatar.png';
            if (!empty($user['gallery'])) {
                $gal = explode(',', $user['gallery']);
                $avatar = 'uploads/' . $user['id'] . '/' . trim($gal[0]);
            }
            $vipClass = $user['vip'] ? 'gold-shimmer' : '';
            
            echo "<div style='display: flex; align-items: center; padding: 5px 10px; background: #f0f0f0; border-radius: 20px; font-size: 12px;'>";
            echo "<img src='$avatar' style='width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;' alt='{$user['username']}'>";
            echo "<span class='$vipClass'>{$user['username']}</span>";
            if ($user['vip']) echo " 👑";
            echo "</div>";
        }
        
        echo "</div>";
    }
    
    echo "<h3>🔗 Links:</h3>";
    echo "<p><a href='test_pvp_avatars.php'>→ Test Avatars & VS Display</a></p>";
    echo "<p><a href='pvp_battles.php'>→ Go to PVP Battles (Visual)</a></p>";
    echo "<p><a href='test_cron.php'>→ Run CRON (Advance Round)</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
