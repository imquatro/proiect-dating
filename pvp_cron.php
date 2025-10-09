<?php
/**
 * PvP Battles Cron Job
 * 
 * Rulează periodic pentru:
 * - Inițierea battle-urilor noi (la fiecare 4 zile)
 * - Avansarea rundelor (la fiecare 1 zi)
 * - Reset lunar al ligilor
 * - Promovare jucători top 4
 * 
 * Rulează cu: php pvp_cron.php
 * Sau configurează în crontab: * * * * * php /path/to/pvp_cron.php
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_helpers.php';

// Log helper
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[$timestamp] $message\n";
    error_log("PvP Cron [$timestamp]: $message");
}

logMessage("=== PvP Cron Job Started ===");

// 1. Verificăm dacă trebuie să facem reset lunar
checkMonthlyReset();

// 2. Verificăm și procesăm battle-urile active
processActiveBattles();

// 3. Verificăm dacă trebuie să începem battle-uri noi
startNewBattles();

logMessage("=== PvP Cron Job Completed ===");

/**
 * Verifică și execută reset-ul lunar al ligilor
 */
function checkMonthlyReset() {
    global $db;
    
    $today = date('Y-m-01'); // Prima zi a lunii curente
    
    // Verificăm dacă s-a făcut deja reset luna aceasta
    $stmt = $db->query("SELECT MAX(last_reset_date) as last_reset FROM user_league_status");
    $lastReset = $stmt->fetchColumn();
    
    if (!$lastReset || $lastReset < $today) {
        logMessage("Executăm reset lunar al ligilor...");
        
        if (monthlyLeagueReset()) {
            logMessage("✓ Reset lunar executat cu succes!");
        } else {
            logMessage("✗ Reset lunar deja executat pentru această lună");
        }
    } else {
        logMessage("Reset lunar deja executat pentru luna aceasta");
    }
}

/**
 * Procesează battle-urile active (avansare runde)
 */
function processActiveBattles() {
    global $db;
    
    $stmt = $db->query("SELECT * FROM pvp_battles WHERE is_active = 1 AND status = 'active'");
    $battles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($battles)) {
        logMessage("Nu sunt battle-uri active de procesat");
        return;
    }
    
    foreach ($battles as $battle) {
        logMessage("Procesăm battle #{$battle['id']} (Liga {$battle['league_id']})");
        
        $battleId = $battle['id'];
        $currentRound = $battle['current_round'];
        $startDate = new DateTime($battle['start_date']);
        $now = new DateTime();
        
        // Calculăm când ar trebui să fie runda curentă
        // Ziua 0-3: Înscrieri (4 zile)
        // Ziua 4: Rundă 1 (1/32)
        // Ziua 5: Rundă 2 (1/16)
        // etc.
        
        $daysSinceStart = $now->diff($startDate)->days;
        $expectedRound = max(1, $daysSinceStart - 3); // -3 pentru zilele de înscriere
        
        if ($expectedRound > $currentRound) {
            // Trebuie să avansăm la runda următoare
            logMessage("Avansăm de la runda $currentRound la runda $expectedRound");
            
            // Procesăm rezultatele rundei curente
            $winners = processRoundResults($battleId, $currentRound);
            logMessage("Rundă $currentRound finalizată. Câștigători: " . count($winners));
            
            // Verificăm dacă am terminat battle-ul (mai rămâne 1 câștigător)
            if (count($winners) == 1) {
                logMessage("Battle finalizat! Câștigător: User ID {$winners[0]['user_id']}");
                
                // Marcăm pozițiile finale
                $db->prepare("UPDATE pvp_participants SET final_position = 1 WHERE battle_id = ? AND user_id = ?")
                    ->execute([$battleId, $winners[0]['user_id']]);
                
                // Promovăm top 4
                promoteTopPlayers($battleId);
                logMessage("Top 4 jucători promovați la liga superioară");
                
                // Marcăm battle-ul ca finalizat
                $db->prepare("UPDATE pvp_battles SET status = 'completed', is_active = 0 WHERE id = ?")
                    ->execute([$battleId]);
                
                logMessage("✓ Battle #{$battleId} marcat ca finalizat");
            } else {
                // Update la runda următoare
                $db->prepare("UPDATE pvp_battles SET current_round = ? WHERE id = ?")
                    ->execute([$expectedRound, $battleId]);
                
                logMessage("✓ Battle avansat la runda $expectedRound");
            }
        } else {
            logMessage("Battle #{$battleId} este la runda corectă ($currentRound)");
        }
    }
}

/**
 * Inițiază battle-uri noi pentru fiecare ligă (la 4 zile)
 */
function startNewBattles() {
    global $db;
    
    // Luăm toate ligile
    $stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
    $leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($leagues as $league) {
        $leagueId = $league['id'];
        $leagueName = $league['name'];
        
        // Verificăm dacă există deja un battle activ pentru liga aceasta
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_battles WHERE league_id = ? AND is_active = 1");
        $stmt->execute([$leagueId]);
        $hasActive = $stmt->fetchColumn() > 0;
        
        if ($hasActive) {
            logMessage("Liga $leagueName deja are un battle activ");
            continue;
        }
        
        // Verificăm când a fost ultimul battle pentru liga aceasta
        $stmt = $db->prepare("SELECT MAX(start_date) as last_start FROM pvp_battles WHERE league_id = ?");
        $stmt->execute([$leagueId]);
        $lastStart = $stmt->fetchColumn();
        
        $shouldStart = false;
        
        if (!$lastStart) {
            // Nu a existat niciodată un battle pentru liga aceasta
            logMessage("Liga $leagueName nu are istoric de battle-uri. Începem primul!");
            $shouldStart = true;
        } else {
            // Verificăm dacă au trecut 4 zile + durata battle-ului anterior
            $lastStartDate = new DateTime($lastStart);
            $now = new DateTime();
            $daysSince = $now->diff($lastStartDate)->days;
            
            // 4 zile + ~6 zile battle = ~10 zile între start-uri
            if ($daysSince >= 10) {
                logMessage("Au trecut $daysSince zile de la ultimul battle în liga $leagueName. Începem unul nou!");
                $shouldStart = true;
            } else {
                logMessage("Liga $leagueName: Mai sunt " . (10 - $daysSince) . " zile până la următorul battle");
            }
        }
        
        if ($shouldStart) {
            // Verificăm numărul minim de jucători
            if (!checkMinimumPlayers($leagueId)) {
                logMessage("✗ Liga $leagueName: Jucători insuficienți pentru a începe battle-ul");
                
                // Creăm un battle "postponed" pentru tracking
                $db->prepare("INSERT INTO pvp_battles (league_id, start_date, status, is_active) VALUES (?, NOW(), 'postponed', 0)")
                    ->execute([$leagueId]);
                
                continue;
            }
            
            logMessage("Începem battle nou pentru liga $leagueName...");
            
            // Creăm battle-ul
            $stmt = $db->prepare("INSERT INTO pvp_battles (league_id, start_date, current_round, status, is_active) VALUES (?, NOW(), 1, 'active', 1)");
            $stmt->execute([$leagueId]);
            $battleId = $db->lastInsertId();
            
            logMessage("Battle #{$battleId} creat pentru liga $leagueName");
            
            // Alocăm 64 jucători random
            $playersCount = allocatePlayers($battleId, $leagueId);
            logMessage("$playersCount jucători alocați în battle #{$battleId}");
            
            // Creăm meciurile pentru prima rundă (1/32)
            createFirstRoundMatches($battleId);
            logMessage("32 meciuri create pentru runda 1/32");
            
            logMessage("✓ Battle #{$battleId} inițiat cu succes pentru liga $leagueName!");
        }
    }
}

/**
 * Funcție principală de executare (poate fi apelată și manual)
 */
function runCron() {
    checkMonthlyReset();
    processActiveBattles();
    startNewBattles();
}

// Dacă scriptul e rulat direct (nu inclus)
if (php_sapi_name() === 'cli') {
    runCron();
}

