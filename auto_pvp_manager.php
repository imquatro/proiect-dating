<?php
/**
 * AUTO PVP MANAGER - Ruleaza automat PVP-ul
 * 
 * Acest script ruleaza automat:
 * 1. Setup DB (coloane lipsa)
 * 2. Enroll useri in PVP
 * 3. Pornește battle-ul
 * 4. Afișează status
 * 
 * Acceseaza: http://localhost/1/auto_pvp_manager.php
 */

echo "<h1>🤖 AUTO PVP MANAGER</h1>";
echo "<p>Running automatic PVP setup and battle start...</p>";

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
    error_log("Auto PVP [$timestamp]: $message");
}

logMessage("=== AUTO PVP MANAGER STARTED ===");

try {
    // STEP 1: Setup Database (auto-add missing columns)
    logMessage("🔧 STEP 1: Setting up database...");
    
    // Add missing columns to users table
    $adminColumns = [
        'auto_account' => 'BOOLEAN DEFAULT 0',
        'is_banned' => 'BOOLEAN DEFAULT 0', 
        'ban_reason' => 'TEXT DEFAULT NULL',
        'ban_end_date' => 'DATETIME DEFAULT NULL',
        'banned_by' => 'INT DEFAULT NULL',
        'banned_at' => 'DATETIME DEFAULT NULL',
        'is_active' => 'BOOLEAN DEFAULT 1'
    ];
    
    foreach ($adminColumns as $column => $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($stmt->rowCount() == 0) {
            $db->exec("ALTER TABLE users ADD COLUMN $column $definition");
            logMessage("✓ Added column $column to users table");
        }
    }
    
    // Add missing columns to other tables
    $stmt = $db->query("SHOW COLUMNS FROM user_league_status LIKE 'qualified_for_league_id'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE user_league_status ADD COLUMN qualified_for_league_id INT DEFAULT NULL AFTER league_id");
        logMessage("✓ Added column qualified_for_league_id to user_league_status");
    }
    
    $stmt = $db->query("SHOW COLUMNS FROM pvp_battles LIKE 'completed_at'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE pvp_battles ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER is_active");
        logMessage("✓ Added column completed_at to pvp_battles");
    }
    
    // Update leagues to testing settings
    $db->exec("UPDATE pvp_leagues SET name = 'Bronze', min_players = 32 WHERE level = 1");
    $db->exec("UPDATE pvp_leagues SET name = 'Platinum', min_players = 32, color = '#E5E4E2' WHERE level = 2");
    $db->exec("UPDATE pvp_leagues SET name = 'Gold', min_players = 32, color = '#FFD700' WHERE level = 3");
    logMessage("✓ Updated leagues to testing settings");
    
    // STEP 2: Enroll all users in Bronze league
    logMessage("👥 STEP 2: Enrolling users in PVP...");
    
    $result = $db->exec("
        INSERT IGNORE INTO user_league_status (user_id, league_id) 
        SELECT id, 1 FROM users 
        WHERE id NOT IN (SELECT user_id FROM user_league_status)
    ");
    
    $stmt = $db->query("SELECT COUNT(*) FROM user_league_status WHERE league_id = 1");
    $bronzeCount = $stmt->fetchColumn();
    logMessage("✓ Enrolled $bronzeCount users in Bronze league");
    
    // STEP 3: Check if battle already exists
    logMessage("🎮 STEP 3: Checking for active battles...");
    
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($activeBattle) {
        logMessage("✓ Active battle found: Battle #{$activeBattle['id']}");
        
        // Check participants
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_participants WHERE battle_id = ?");
        $stmt->execute([$activeBattle['id']]);
        $participantCount = $stmt->fetchColumn();
        
        if ($participantCount == 0) {
            logMessage("⚠️ Battle exists but has no participants. Recreating...");
            
            // Delete empty battle
            $db->prepare("DELETE FROM pvp_battles WHERE id = ?")->execute([$activeBattle['id']]);
            $activeBattle = null;
        } else {
            logMessage("✓ Battle has $participantCount participants");
        }
    }
    
    // STEP 4: Start new battle if needed
    if (!$activeBattle) {
        logMessage("🚀 STEP 4: Starting new battle...");
        
        if ($bronzeCount >= 32) {
            // Create battle
            $stmt = $db->prepare("INSERT INTO pvp_battles (league_id, start_date, current_round, status, is_active) VALUES (?, NOW(), 1, 'active', 1)");
            $stmt->execute([1]);
            $battleId = $db->lastInsertId();
            
            logMessage("✓ Battle #$battleId created for Bronze league");
            
            // Allocate 32 random players
            try {
                $playersCount = allocatePlayers($battleId, 1);
                logMessage("✓ Allocated $playersCount players for battle #$battleId");
            } catch (Exception $e) {
                logMessage("❌ Error allocating players: " . $e->getMessage());
                throw $e;
            }
            
            // Create matches
            createFirstRoundMatches($battleId);
            logMessage("✓ Created 16 matches for round 1/16");
            
            $activeBattle = ['id' => $battleId, 'league_id' => 1, 'current_round' => 1, 'status' => 'active'];
        } else {
            logMessage("❌ Not enough players: $bronzeCount/32 needed");
        }
    }
    
    // STEP 5: Show final status
    logMessage("📊 STEP 5: Final status...");
    
    if ($activeBattle) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_participants WHERE battle_id = ?");
        $stmt->execute([$activeBattle['id']]);
        $participantCount = $stmt->fetchColumn();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_matches WHERE battle_id = ?");
        $stmt->execute([$activeBattle['id']]);
        $matchCount = $stmt->fetchColumn();
        
        echo "<h2>✅ PVP BATTLE READY!</h2>";
        echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>🎮 Battle Details:</h3>";
        echo "<ul>";
        echo "<li><strong>Battle ID:</strong> {$activeBattle['id']}</li>";
        echo "<li><strong>League:</strong> Bronze</li>";
        echo "<li><strong>Current Round:</strong> {$activeBattle['current_round']}</li>";
        echo "<li><strong>Status:</strong> {$activeBattle['status']}</li>";
        echo "<li><strong>Participants:</strong> $participantCount</li>";
        echo "<li><strong>Matches:</strong> $matchCount</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h3>🔗 Quick Links:</h3>";
        echo "<p><a href='test_pvp_avatars.php' style='background: #f6cf49; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🎮 Test Avatars & VS</a></p>";
        echo "<p><a href='pvp_battles.php' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>🏆 PVP Battles (Visual)</a></p>";
        echo "<p><a href='check_battle_participants.php' style='background: #ff9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px; display: inline-block;'>👥 Check Participants</a></p>";
        
    } else {
        echo "<h2>❌ PVP BATTLE NOT READY</h2>";
        echo "<p>Need more players: $bronzeCount/32</p>";
        echo "<p><a href='farm_admin/panel.php'>→ Add More Users</a></p>";
    }
    
    logMessage("=== AUTO PVP MANAGER COMPLETED ===");
    
} catch (Exception $e) {
    logMessage("❌ ERROR: " . $e->getMessage());
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
