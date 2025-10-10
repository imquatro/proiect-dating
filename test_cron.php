<?php
/**
 * Test PVP CRON - Start Battle
 * 
 * Acceseaza: http://localhost/1/test_cron.php
 */

echo "<h1>PVP CRON Test - Start Battle</h1>";
echo "<p>Executing PVP CRON to start battle...</p>";

// Include pvp_helpers.php for functions
require_once 'includes/db.php';
require_once 'includes/pvp_helpers.php';

// Define constants (same as pvp_cron.php)
define('BATTLE_DURATION_MINUTES', 5);
define('PAUSE_BETWEEN_ROUNDS_MINUTES', 1);
define('LEAGUE_RESET_DAYS', 2);
define('FINAL_DISPLAY_MINUTES', 5);

// Log helper
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message<br>";
    error_log("PvP Cron [$timestamp]: $message");
}

logMessage("=== PvP Cron Job Started ===");

// CRON Functions (copied from pvp_cron.php)
function checkPeriodicReset() {
    global $db;
    
    $stmt = $db->query("SELECT MAX(last_reset_date) as last_reset FROM user_league_status");
    $lastReset = $stmt->fetchColumn();
    
    if ($lastReset) {
        $daysSince = (strtotime('now') - strtotime($lastReset)) / 86400;
        if ($daysSince < LEAGUE_RESET_DAYS) {
            logMessage("Reset ligi: Mai sunt " . round(LEAGUE_RESET_DAYS - $daysSince, 1) . " zile pana la urmatorul reset");
            return;
        }
    }
    
    logMessage("Executam reset periodic al ligilor (la " . LEAGUE_RESET_DAYS . " zile)...");
    
    if (periodicLeagueReset()) {
        logMessage("✓ Reset periodic executat cu succes! Toti jucatorii in Bronze.");
    } else {
        logMessage("✗ Reset periodic deja executat recent");
    }
}

function processActiveBattles() {
    global $db;
    
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 AND status = 'active'");
    $battles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($battles)) {
        logMessage("Nu sunt battle-uri active de procesat");
        return;
    }
    
    foreach ($battles as $battle) {
        logMessage("Procesam battle #{$battle['id']} (Liga {$battle['league_id']})");
        
        $battleId = $battle['id'];
        $currentRound = $battle['current_round'];
        $startDate = new DateTime($battle['start_date']);
        $now = new DateTime();
        
        // Calculam cand ar trebui sa fie runda curenta
        $minutesSinceStart = $now->getTimestamp() - $startDate->getTimestamp();
        $minutesSinceStart = floor($minutesSinceStart / 60);
        
        $minutesPerRound = BATTLE_DURATION_MINUTES + PAUSE_BETWEEN_ROUNDS_MINUTES;
        $expectedRound = floor($minutesSinceStart / $minutesPerRound) + 1;
        
        if ($expectedRound > $currentRound) {
            logMessage("Avansam de la runda $currentRound la runda $expectedRound");
            
            $winners = processRoundResults($battleId, $currentRound);
            logMessage("Runda $currentRound finalizata. Castigatori: " . count($winners));
            
            if (count($winners) == 1) {
                logMessage("Battle finalizat! Castigator: User ID {$winners[0]['user_id']}");
                
                $db->prepare("UPDATE pvp_participants SET final_position = 1 WHERE battle_id = ? AND user_id = ?")
                    ->execute([$battleId, $winners[0]['user_id']]);
                
                promoteTopPlayers($battleId);
                logMessage("Top 4 jucatori (semifinalisti) marcati ca 'qualified' pentru liga superioara");
                
                $leagueId = $battle['league_id'];
                $nextLeagueId = min($leagueId + 1, 3);
                if ($nextLeagueId > $leagueId) {
                    if (checkAndPromoteQualified($nextLeagueId)) {
                        logMessage("✓ Liga $nextLeagueId: 32+ jucatori calificati! Promovare efectiva realizata.");
                    } else {
                        logMessage("Liga $nextLeagueId: Jucatori calificati insuficienti. Raman in liga curenta.");
                    }
                }
                
                $db->prepare("UPDATE pvp_battles SET status = 'displaying_final', completed_at = NOW() WHERE id = ?")
                    ->execute([$battleId]);
                
                logMessage("✓ Battle #{$battleId} - FINALA COMPLETATA. Afisare " . FINAL_DISPLAY_MINUTES . " min.");
            } else {
                $db->prepare("UPDATE pvp_battles SET current_round = ? WHERE id = ?")
                    ->execute([$expectedRound, $battleId]);
                
                logMessage("✓ Battle avansat la runda $expectedRound");
            }
        } else {
            logMessage("Battle #{$battleId} este la runda corecta ($currentRound)");
        }
    }
}

function startNewBattles() {
    global $db;
    
    $stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
    $leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($leagues as $league) {
        $leagueId = $league['id'];
        $leagueName = $league['name'];
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_battles WHERE league_id = ? AND is_active = 1");
        $stmt->execute([$leagueId]);
        $hasActive = $stmt->fetchColumn() > 0;
        
        if ($hasActive) {
            logMessage("Liga $leagueName deja are un battle activ");
            continue;
        }
        
        if (!checkMinimumPlayers($leagueId)) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_league_status WHERE league_id = ?");
            $stmt->execute([$leagueId]);
            $playerCount = $stmt->fetchColumn();
            logMessage("Liga $leagueName: $playerCount jucatori (minim 32 necesari)");
            continue;
        }
        
        logMessage("✓ Liga $leagueName: 32+ jucatori! Incepem battle IMEDIAT...");
        
        $stmt = $db->prepare("INSERT INTO pvp_battles (league_id, start_date, current_round, status, is_active) VALUES (?, NOW(), 1, 'active', 1)");
        $stmt->execute([$leagueId]);
        $battleId = $db->lastInsertId();
        
        logMessage("Battle #{$battleId} creat pentru liga $leagueName");
        
        $playersCount = allocatePlayers($battleId, $leagueId);
        logMessage("$playersCount jucatori alocati in battle #{$battleId}");
        
        createFirstRoundMatches($battleId);
        logMessage("16 meciuri create pentru runda 1/16");
        
        logMessage("✓ Battle #{$battleId} initiat cu succes pentru liga $leagueName!");
    }
}

// Execute CRON functions
try {
    // 1. Check periodic reset
    checkPeriodicReset();
    
    // 2. Process active battles
    processActiveBattles();
    
    // 3. Start new battles
    startNewBattles();
    
    logMessage("=== PvP Cron Job Completed ===");
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
}

echo "<h2>✅ CRON Executed!</h2>";
echo "<p>Check the output above ↑ for battle creation status.</p>";

// Check if battle was created
require_once 'includes/db.php';

$stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
$activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);

if ($activeBattle) {
    echo "<h3>🎮 Active Battle Found:</h3>";
    echo "<ul>";
    echo "<li><strong>Battle ID:</strong> {$activeBattle['id']}</li>";
    echo "<li><strong>League ID:</strong> {$activeBattle['league_id']}</li>";
    echo "<li><strong>Current Round:</strong> {$activeBattle['current_round']}</li>";
    echo "<li><strong>Status:</strong> {$activeBattle['status']}</li>";
    echo "<li><strong>Start Date:</strong> {$activeBattle['start_date']}</li>";
    echo "</ul>";
    
    // Check participants
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_participants WHERE battle_id = ?");
    $stmt->execute([$activeBattle['id']]);
    $participantCount = $stmt->fetchColumn();
    
    // Check matches
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_matches WHERE battle_id = ?");
    $stmt->execute([$activeBattle['id']]);
    $matchCount = $stmt->fetchColumn();
    
    echo "<h3>📊 Battle Details:</h3>";
    echo "<ul>";
    echo "<li><strong>Participants:</strong> $participantCount</li>";
    echo "<li><strong>Matches Created:</strong> $matchCount</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Next Steps:</h3>";
    echo "<p><a href='test_pvp_avatars.php'>→ Test Avatars & VS Display</a></p>";
    echo "<p><a href='pvp_battles.php'>→ Go to PVP Battles (Visual)</a></p>";
    
} else {
    echo "<h3>❌ No Active Battle Found</h3>";
    echo "<p>Check the CRON output above for errors.</p>";
    echo "<p><a href='enroll_existing_users_pvp.php'>→ Check User Enrollment</a></p>";
}

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>→ Back to Admin Panel</a></p>";
?>
