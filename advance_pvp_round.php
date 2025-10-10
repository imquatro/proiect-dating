<?php
/**
 * ADVANCE PVP ROUND - Avanseaza runda curenta
 * 
 * Acceseaza: http://localhost/1/advance_pvp_round.php
 */

echo "<h1>⏭️ ADVANCE PVP ROUND</h1>";

require_once 'includes/db.php';
require_once 'includes/pvp_helpers.php';

// Define constants
define('BATTLE_DURATION_MINUTES', 5);
define('PAUSE_BETWEEN_ROUNDS_MINUTES', 1);
define('LEAGUE_RESET_DAYS', 2);
define('FINAL_DISPLAY_MINUTES', 5);

// Log helper
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message<br>";
}

logMessage("=== ADVANCE PVP ROUND STARTED ===");

try {
    // Get active battle
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 AND status = 'active' ORDER BY id DESC LIMIT 1");
    $activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$activeBattle) {
        echo "<h2>❌ No Active Battle Found</h2>";
        echo "<p><a href='auto_pvp_manager.php'>→ Start PVP Battle</a></p>";
        exit;
    }
    
    echo "<h2>🎮 Active Battle Found</h2>";
    echo "<p><strong>Battle ID:</strong> {$activeBattle['id']}</p>";
    echo "<p><strong>Current Round:</strong> {$activeBattle['current_round']}</p>";
    echo "<p><strong>Status:</strong> {$activeBattle['status']}</p>";
    
    $battleId = $activeBattle['id'];
    $currentRound = $activeBattle['current_round'];
    
    // Force advance to next round
    $nextRound = $currentRound + 1;
    
    logMessage("🚀 Force advancing from round $currentRound to round $nextRound...");
    
    // Process current round results
    $winners = processRoundResults($battleId, $currentRound);
    logMessage("✓ Round $currentRound completed. Winners: " . count($winners));
    
    if (count($winners) == 1) {
        // Battle finished
        logMessage("🏆 BATTLE FINISHED! Winner: User ID {$winners[0]['user_id']}");
        
        // Mark final position
        $db->prepare("UPDATE pvp_participants SET final_position = 1 WHERE battle_id = ? AND user_id = ?")
            ->execute([$battleId, $winners[0]['user_id']]);
        
        // Promote top 4
        promoteTopPlayers($battleId);
        logMessage("✓ Top 4 players marked as qualified for next league");
        
        // Mark as displaying final
        $db->prepare("UPDATE pvp_battles SET status = 'displaying_final', completed_at = NOW() WHERE id = ?")
            ->execute([$battleId]);
        
        echo "<h2>🏆 BATTLE COMPLETED!</h2>";
        echo "<p><strong>Winner:</strong> User ID {$winners[0]['user_id']}</p>";
        echo "<p><strong>Status:</strong> Displaying final results</p>";
        
    } else {
        // Advance to next round
        $db->prepare("UPDATE pvp_battles SET current_round = ? WHERE id = ?")
            ->execute([$nextRound, $battleId]);
        
        logMessage("✓ Advanced to round $nextRound");
        
        echo "<h2>✅ ROUND ADVANCED!</h2>";
        echo "<p><strong>From Round:</strong> $currentRound</p>";
        echo "<p><strong>To Round:</strong> $nextRound</p>";
        echo "<p><strong>Winners:</strong> " . count($winners) . " players</p>";
    }
    
    // Show current battle status
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_participants WHERE battle_id = ?");
    $stmt->execute([$battleId]);
    $participantCount = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_matches WHERE battle_id = ? AND round_number = ?");
    $stmt->execute([$battleId, $nextRound]);
    $nextRoundMatches = $stmt->fetchColumn();
    
    echo "<h3>📊 Battle Status:</h3>";
    echo "<ul>";
    echo "<li><strong>Total Participants:</strong> $participantCount</li>";
    echo "<li><strong>Current Round:</strong> $nextRound</li>";
    echo "<li><strong>Next Round Matches:</strong> $nextRoundMatches</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Quick Links:</h3>";
    echo "<p><a href='test_pvp_avatars.php' style='background: #f6cf49; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🎮 Test Avatars & VS</a></p>";
    echo "<p><a href='pvp_battles.php' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🏆 PVP Battles (Visual)</a></p>";
    echo "<p><a href='advance_pvp_round.php' style='background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>⏭️ Advance Next Round</a></p>";
    
    logMessage("=== ADVANCE PVP ROUND COMPLETED ===");
    
} catch (Exception $e) {
    logMessage("❌ ERROR: " . $e->getMessage());
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
