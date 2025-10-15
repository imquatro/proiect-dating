<?php
require_once __DIR__ . '/db.php';

// Auto-create PvP tables if they don't exist
try {
    // Tabela ligi
    $db->exec("CREATE TABLE IF NOT EXISTS pvp_leagues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        level INT NOT NULL,
        color VARCHAR(20) NOT NULL,
        min_players INT NOT NULL DEFAULT 64,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabela battles
    $db->exec("CREATE TABLE IF NOT EXISTS pvp_battles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        league_id INT NOT NULL,
        start_date DATETIME NOT NULL,
        current_round INT NOT NULL DEFAULT 1,
        status ENUM('pending', 'active', 'completed', 'postponed', 'displaying_final') DEFAULT 'pending',
        is_active BOOLEAN DEFAULT 1,
        completed_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (league_id) REFERENCES pvp_leagues(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Adaugam coloana completed_at daca nu exista
    $stmt = $db->query("SHOW COLUMNS FROM pvp_battles LIKE 'completed_at'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE pvp_battles ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER is_active");
        error_log("PvP: Added column completed_at to pvp_battles");
    }
    
    // Modificam ENUM pentru status daca nu contine 'displaying_final'
    $stmt = $db->query("SHOW COLUMNS FROM pvp_battles LIKE 'status'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($column && strpos($column['Type'], 'displaying_final') === false) {
        $db->exec("ALTER TABLE pvp_battles MODIFY COLUMN status ENUM('pending', 'active', 'completed', 'postponed', 'displaying_final') DEFAULT 'pending'");
        error_log("PvP: Updated status ENUM to include 'displaying_final'");
    }

    // Tabela participanti
    $db->exec("CREATE TABLE IF NOT EXISTS pvp_participants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        battle_id INT NOT NULL,
        user_id INT NOT NULL,
        popularity_score INT NOT NULL DEFAULT 0,
        eliminated_in_round INT DEFAULT NULL,
        final_position INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (battle_id) REFERENCES pvp_battles(id) ON DELETE CASCADE,
        UNIQUE KEY unique_battle_user (battle_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabela meciuri
    $db->exec("CREATE TABLE IF NOT EXISTS pvp_matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        battle_id INT NOT NULL,
        round_number INT NOT NULL,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        winner_id INT DEFAULT NULL,
        user1_score INT NOT NULL DEFAULT 0,
        user2_score INT NOT NULL DEFAULT 0,
        match_date DATETIME DEFAULT NULL,
        completed BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (battle_id) REFERENCES pvp_battles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Tabela status liga user
    $db->exec("CREATE TABLE IF NOT EXISTS user_league_status (
        user_id INT PRIMARY KEY,
        league_id INT NOT NULL DEFAULT 1,
        qualified_for_league_id INT DEFAULT NULL,
        last_reset_date DATE DEFAULT NULL,
        total_wins INT NOT NULL DEFAULT 0,
        total_losses INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (league_id) REFERENCES pvp_leagues(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Adaugam coloana qualified_for_league_id daca nu exista
    $stmt = $db->query("SHOW COLUMNS FROM user_league_status LIKE 'qualified_for_league_id'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE user_league_status ADD COLUMN qualified_for_league_id INT DEFAULT NULL AFTER league_id");
        error_log("PvP: Added column qualified_for_league_id to user_league_status");
    }
    
    // Adaugam coloanele necesare pentru admin system in tabela users
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
            error_log("PvP: Added column $column to users table");
        }
    }
    
    // Adaugam userii noi in user_league_status (Bronze league)
    $db->exec("
        INSERT IGNORE INTO user_league_status (user_id, league_id) 
        SELECT id, 1 FROM users 
        WHERE id NOT IN (SELECT user_id FROM user_league_status)
    ");

    // Tabela chat meciuri (efemer - se sterge dupa finalizare)
    $db->exec("CREATE TABLE IF NOT EXISTS pvp_match_chat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        match_id INT NOT NULL,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (match_id) REFERENCES pvp_matches(id) ON DELETE CASCADE,
        INDEX (match_id, created_at),
        INDEX (match_id, is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Inseram ligile default daca nu exista
    $count = $db->query("SELECT COUNT(*) FROM pvp_leagues")->fetchColumn();
    if ($count == 0) {
        $db->exec("INSERT INTO pvp_leagues (name, level, color, min_players) VALUES 
            ('Bronze', 1, '#CD7F32', 32),
            ('Platinum', 2, '#E5E4E2', 32),
            ('Gold', 3, '#FFD700', 32)");
        error_log("PvP: Created default leagues (Bronze, Platinum, Gold)");
    } else {
        // Actualizam ligile existente la setarile de testare
        $db->exec("UPDATE pvp_leagues SET name = 'Bronze', min_players = 32 WHERE level = 1");
        $db->exec("UPDATE pvp_leagues SET name = 'Platinum', min_players = 32, color = '#E5E4E2' WHERE level = 2");
        
        // Verificam daca exista liga Gold (level 3)
        $goldExists = $db->query("SELECT COUNT(*) FROM pvp_leagues WHERE level = 3")->fetchColumn();
        if ($goldExists == 0) {
            $db->exec("INSERT INTO pvp_leagues (name, level, color, min_players) VALUES ('Gold', 3, '#FFD700', 32)");
            error_log("PvP: Added Gold league");
        } else {
            $db->exec("UPDATE pvp_leagues SET name = 'Gold', min_players = 32, color = '#FFD700' WHERE level = 3");
        }
        error_log("PvP: Updated existing leagues to testing settings (32 min_players)");
    }

} catch (PDOException $e) {
    error_log("PvP Tables Error: " . $e->getMessage());
}

/**
 * Calculeaza scorul de popularitate pentru un user
 */
function calculatePopularityScore($userId) {
    global $db;
    $score = 0;
    
    // Check if help_records table exists
    $stmt = $db->query("SHOW TABLES LIKE 'help_records'");
    if ($stmt->rowCount() == 0) {
        // Table doesn't exist, return random score for testing
        $score = rand(0, 10);
        error_log("PVP: help_records table not found, using random score: $score for user $userId");
        return $score;
    }
    
    try {
        // Ajutoare oferite (1 punct per 100 ajutoare) - toate timpurile
        $stmt = $db->prepare("SELECT COUNT(*) FROM help_records WHERE helper_user_id = ?");
        $stmt->execute([$userId]);
        $helpsGiven = $stmt->fetchColumn();
        $score += intval($helpsGiven / 100); // 1 punct per 100 ajutoare
        
        // Ajutoare primite (1 punct per 300 ajutoare) - toate timpurile
        $stmt = $db->prepare("SELECT COUNT(*) FROM help_records WHERE helped_user_id = ?");
        $stmt->execute([$userId]);
        $helpsReceived = $stmt->fetchColumn();
        $score += intval($helpsReceived / 300); // 1 punct per 300 ajutoare
        
        // BONUS: Milestone-uri pentru persoane diferite ajutate
        $stmt = $db->prepare("SELECT COUNT(DISTINCT helped_user_id) FROM help_records WHERE helper_user_id = ?");
        $stmt->execute([$userId]);
        $uniqueHelped = $stmt->fetchColumn();
        
        // Puncte pentru milestone-uri: 3, 5, 10, 15, 20 (se oprește la 20)
        $milestones = [3, 5, 10, 15, 20];
        foreach ($milestones as $milestone) {
            if ($uniqueHelped >= $milestone) {
                $score += 1; // 1 punct pentru fiecare milestone atins
            }
        }
        
        // BONUS: Persoane care au încredere în tine (1 punct per 5 persoane care te-au ajutat de 10+ ori)
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT helper_user_id) 
            FROM help_records 
            WHERE helped_user_id = ? 
            AND helper_user_id IN (
                SELECT helper_user_id 
                FROM help_records 
                WHERE helped_user_id = ? 
                GROUP BY helper_user_id 
                HAVING COUNT(*) >= 10
            )
        ");
        $stmt->execute([$userId, $userId]);
        $trustedHelpers = $stmt->fetchColumn();
        $score += intval($trustedHelpers / 5); // 1 punct per 5 persoane de încredere
        
    } catch (Exception $e) {
        // If any error, use random score for testing
        $score = rand(0, 10);
        error_log("PVP: Error calculating popularity score for user $userId: " . $e->getMessage() . ", using random score: $score");
    }
    
    return $score;
}

/**
 * Verifica daca sunt suficienti jucatori pentru a incepe un battle
 */
function checkMinimumPlayers($leagueId) {
    global $db;
    
    $stmt = $db->prepare("SELECT min_players FROM pvp_leagues WHERE id = ?");
    $stmt->execute([$leagueId]);
    $minPlayers = $stmt->fetchColumn();
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_league_status WHERE league_id = ?");
    $stmt->execute([$leagueId]);
    $totalPlayers = $stmt->fetchColumn();
    
    return $totalPlayers >= $minPlayers;
}

/**
 * Aloca random 32 jucatori pentru un battle (din toți jucătorii disponibili)
 */
function allocatePlayers($battleId, $leagueId) {
    global $db;
    
    // Selectam 32 jucatori random din liga respectiva (din toți cei disponibili)
    $stmt = $db->prepare("
        SELECT u.id, u.username 
        FROM users u
        JOIN user_league_status uls ON u.id = uls.user_id
        WHERE uls.league_id = ? AND u.is_active = 1
        ORDER BY RAND()
        LIMIT 32
    ");
    $stmt->execute([$leagueId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculam si salvam scorul de popularitate pentru fiecare
    $stmt = $db->prepare("INSERT INTO pvp_participants (battle_id, user_id, popularity_score) VALUES (?, ?, ?)");
    foreach ($players as $player) {
        $score = calculatePopularityScore($player['id']);
        $stmt->execute([$battleId, $player['id'], $score]);
    }
    
    // Log pentru debugging
    error_log("PVP: Allocated " . count($players) . " players for battle #$battleId in league #$leagueId");
    
    return count($players);
}

/**
 * Creeaza meciurile pentru prima runda (1/16 - 32 jucatori)
 */
function createFirstRoundMatches($battleId) {
    global $db;
    
    // Luam toti participantii sortati aleatoriu
    $stmt = $db->prepare("SELECT user_id, popularity_score FROM pvp_participants WHERE battle_id = ? ORDER BY RAND()");
    $stmt->execute([$battleId]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cream 16 meciuri (32 jucatori / 2) = Runda 1/16
    $stmt = $db->prepare("INSERT INTO pvp_matches (battle_id, round_number, user1_id, user2_id, user1_score, user2_score) VALUES (?, 1, ?, ?, ?, ?)");
    
    for ($i = 0; $i < count($participants); $i += 2) {
        if (isset($participants[$i + 1])) {
            $user1 = $participants[$i];
            $user2 = $participants[$i + 1];
            
            $stmt->execute([
                $battleId,
                $user1['user_id'],
                $user2['user_id'],
                $user1['popularity_score'],
                $user2['popularity_score']
            ]);
        }
    }
}

/**
 * Determina castigatorii unei runde si avanseaza la urmatoarea
 */
function processRoundResults($battleId, $roundNumber) {
    global $db;
    
    // Luam toate meciurile rundei
    $stmt = $db->prepare("SELECT id, user1_id, user2_id, user1_score, user2_score FROM pvp_matches WHERE battle_id = ? AND round_number = ?");
    $stmt->execute([$battleId, $roundNumber]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $winners = [];
    $updateStmt = $db->prepare("UPDATE pvp_matches SET winner_id = ?, completed = 1, match_date = NOW() WHERE id = ?");
    $eliminateStmt = $db->prepare("UPDATE pvp_participants SET eliminated_in_round = ? WHERE battle_id = ? AND user_id = ?");
    
    foreach ($matches as $match) {
        // Determină câștigătorul
        $isTie = false;
        if ($match['user1_score'] > $match['user2_score']) {
            $winnerId = $match['user1_id'];
            error_log("PVP Match #{$match['id']}: User {$match['user1_id']} wins with score {$match['user1_score']} vs {$match['user2_score']}");
        } elseif ($match['user2_score'] > $match['user1_score']) {
            $winnerId = $match['user2_id'];
            error_log("PVP Match #{$match['id']}: User {$match['user2_id']} wins with score {$match['user2_score']} vs {$match['user1_score']}");
        } else {
            // EGALITATE - Alegeți random câștigătorul (50/50 șansă)
            $isTie = true;
            $winnerId = (rand(0, 1) === 0) ? $match['user1_id'] : $match['user2_id'];
            error_log("PVP Match #{$match['id']}: TIE at {$match['user1_score']} - Random winner chosen: User {$winnerId}");
        }
        $loserId = $winnerId == $match['user1_id'] ? $match['user2_id'] : $match['user1_id'];
        
        // Update match with winner
        $updateStmt->execute([$winnerId, $match['id']]);
        
        // Mark loser as eliminated in this round
        $eliminateStmt->execute([$roundNumber, $battleId, $loserId]);
        
        error_log("PVP Match #{$match['id']}: Winner={$winnerId}, Loser={$loserId}, Round={$roundNumber}");
        
        $winners[] = [
            'user_id' => $winnerId,
            'score' => $winnerId == $match['user1_id'] ? $match['user1_score'] : $match['user2_score']
        ];
    }
    
    // Daca mai sunt castigatori, cream runda urmatoare
    if (count($winners) > 1) {
        $nextRound = $roundNumber + 1;
        $insertStmt = $db->prepare("INSERT INTO pvp_matches (battle_id, round_number, user1_id, user2_id, user1_score, user2_score) VALUES (?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($winners); $i += 2) {
            if (isset($winners[$i + 1])) {
                $insertStmt->execute([
                    $battleId,
                    $nextRound,
                    $winners[$i]['user_id'],
                    $winners[$i + 1]['user_id'],
                    $winners[$i]['score'],
                    $winners[$i + 1]['score']
                ]);
            }
        }
    }
    
    return $winners;
}

/**
 * Salvează câștigătorul turneului în tabela pentru achievements
 */
function saveTournamentWinner($battleId, $leagueId) {
    global $db;
    
    try {
        // Găsește câștigătorul din finală
        $stmt = $db->prepare("
            SELECT m.*, u.username, l.name as league_name
            FROM pvp_matches m
            JOIN users u ON m.winner_id = u.id
            JOIN pvp_leagues l ON l.id = ?
            WHERE m.battle_id = ? AND m.round_number = 5 AND m.completed = 1 AND m.winner_id IS NOT NULL
        ");
        $stmt->execute([$leagueId, $battleId]);
        $finalMatch = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$finalMatch) {
            error_log("PvP: No winner found for battle #$battleId");
            return false;
        }
        
        // Numără participanții totali
        $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_participants WHERE battle_id = ?");
        $stmt->execute([$battleId]);
        $totalParticipants = $stmt->fetchColumn();
        
        // Salvează câștigătorul
        $stmt = $db->prepare("
            INSERT INTO pvp_tournament_winners 
            (user_id, username, league_id, league_name, battle_id, tournament_date, final_score, opponent_score, total_participants)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)
        ");
        
        $winnerScore = $finalMatch['user1_id'] == $finalMatch['winner_id'] ? $finalMatch['user1_score'] : $finalMatch['user2_score'];
        $opponentScore = $finalMatch['user1_id'] == $finalMatch['winner_id'] ? $finalMatch['user2_score'] : $finalMatch['user1_score'];
        
        $stmt->execute([
            $finalMatch['winner_id'],
            $finalMatch['username'],
            $leagueId,
            $finalMatch['league_name'],
            $battleId,
            $winnerScore,
            $opponentScore,
            $totalParticipants
        ]);
        
        error_log("PvP: Saved tournament winner - {$finalMatch['username']} won battle #$battleId in {$finalMatch['league_name']} league");
        return true;
        
    } catch (Exception $e) {
        error_log("PvP: Error saving tournament winner: " . $e->getMessage());
        return false;
    }
}

/**
 * Marcheaza top 4 jucatori (semifinalisti) ca "qualified" pentru liga superioara
 * NU ii promoveaza efectiv pana cand liga superioara are 32+ jucatori calificati
 */
function promoteTopPlayers($battleId) {
    global $db;
    
    // Gasim semifinala si finala
    $stmt = $db->prepare("SELECT MAX(round_number) as final_round FROM pvp_matches WHERE battle_id = ?");
    $stmt->execute([$battleId]);
    $finalRound = $stmt->fetchColumn();
    
    $semifinalRound = $finalRound - 1;
    
    // Top 4: participantii din semifinale (cei care au ajuns in semifinala)
    $stmt = $db->prepare("
        SELECT DISTINCT user1_id as user_id FROM pvp_matches WHERE battle_id = ? AND round_number = ?
        UNION
        SELECT DISTINCT user2_id as user_id FROM pvp_matches WHERE battle_id = ? AND round_number = ?
    ");
    $stmt->execute([$battleId, $semifinalRound, $battleId, $semifinalRound]);
    $top4 = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Gasim liga curenta
    $stmt = $db->prepare("SELECT league_id FROM pvp_battles WHERE id = ?");
    $stmt->execute([$battleId]);
    $currentLeagueId = $stmt->fetchColumn();
    
    // Liga urmatoare (max 3 = Gold)
    $nextLeagueId = min($currentLeagueId + 1, 3);
    
    if ($nextLeagueId > $currentLeagueId) {
        // NU ii mutam direct, doar ii marcam ca "qualified"
        $updateStmt = $db->prepare("
            UPDATE user_league_status 
            SET qualified_for_league_id = ? 
            WHERE user_id = ?
        ");
        foreach ($top4 as $userId) {
            $updateStmt->execute([$nextLeagueId, $userId]);
        }
    }
}

/**
 * Verifica si promoveaza efectiv jucatorii calificati daca sunt 32+
 */
function checkAndPromoteQualified($leagueId) {
    global $db;
    
    // Verifica cati jucatori sunt calificati pentru aceasta liga
    $stmt = $db->prepare("SELECT COUNT(*) FROM user_league_status WHERE qualified_for_league_id = ?");
    $stmt->execute([$leagueId]);
    $qualifiedCount = $stmt->fetchColumn();
    
    // Daca sunt 32+, ii promovam efectiv
    if ($qualifiedCount >= 32) {
        $db->prepare("
            UPDATE user_league_status 
            SET league_id = qualified_for_league_id, qualified_for_league_id = NULL 
            WHERE qualified_for_league_id = ?
        ")->execute([$leagueId]);
        return true;
    }
    return false;
}

/**
 * Reset periodic - redistribuie toti jucatorii la Bronze (la fiecare LEAGUE_RESET_DAYS zile)
 */
function periodicLeagueReset() {
    global $db;
    
    // Verificam daca s-a facut deja reset recent
    $stmt = $db->query("SELECT MAX(last_reset_date) FROM user_league_status");
    $lastReset = $stmt->fetchColumn();
    
    if ($lastReset) {
        $daysSince = (strtotime('now') - strtotime($lastReset)) / 86400;
        if ($daysSince < LEAGUE_RESET_DAYS) {
            return false; // Prea devreme pentru reset
        }
    }
    
    // Resetam toti userii la liga Bronze si stergem calificarile
    $db->exec("UPDATE user_league_status SET league_id = 1, qualified_for_league_id = NULL, last_reset_date = CURDATE()");
    
    return true;
}

/**
 * Reset lunar - redistribuie toti jucatorii (DEPRECATED - foloseste periodicLeagueReset)
 */
function monthlyLeagueReset() {
    return periodicLeagueReset();
}

/**
 * Obtine status-ul battle-ului curent pentru un user
 */
function getUserBattleStatus($userId) {
    global $db;
    
    // Gasim liga userului
    $stmt = $db->prepare("SELECT league_id FROM user_league_status WHERE user_id = ?");
    $stmt->execute([$userId]);
    $leagueId = $stmt->fetchColumn();
    
    if (!$leagueId) {
        // Daca nu e in nicio liga, il punem in bronz
        $db->prepare("INSERT INTO user_league_status (user_id, league_id) VALUES (?, 1) ON DUPLICATE KEY UPDATE league_id = 1")->execute([$userId]);
        $leagueId = 1;
    }
    
    // Gasim battle-ul activ pentru liga sa
    $stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$leagueId]);
    $battle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$battle) {
        return null;
    }
    
    // Verificam daca userul participa
    $stmt = $db->prepare("SELECT * FROM pvp_participants WHERE battle_id = ? AND user_id = ?");
    $stmt->execute([$battle['id'], $userId]);
    $participation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$participation) {
        return null;
    }
    
    // Gasim meciul curent al userului
    $stmt = $db->prepare("
        SELECT * FROM pvp_matches 
        WHERE battle_id = ? AND round_number = ? 
        AND (user1_id = ? OR user2_id = ?) 
        AND completed = 0
        LIMIT 1
    ");
    $stmt->execute([$battle['id'], $battle['current_round'], $userId, $userId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'battle' => $battle,
        'participation' => $participation,
        'current_match' => $match,
        'league_id' => $leagueId
    ];
}

/**
 * Obtine numarul de mesaje necitite in meciul userului
 */
function getUnreadMatchChatCount($userId) {
    global $db;
    $status = getUserBattleStatus($userId);
    
    if (!$status || !$status['current_match']) {
        return 0;
    }
    
    $matchId = $status['current_match']['id'];
    
    // Numaram mesajele necitite trimise de adversar
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM pvp_match_chat 
        WHERE match_id = ? AND user_id != ? AND is_read = 0
    ");
    $stmt->execute([$matchId, $userId]);
    
    return (int)$stmt->fetchColumn();
}

/**
 * Marcheaza mesajele dintr-un meci ca citite pentru un user
 */
function markMatchChatAsRead($matchId, $userId) {
    global $db;
    
    $stmt = $db->prepare("
        UPDATE pvp_match_chat 
        SET is_read = 1 
        WHERE match_id = ? AND user_id != ?
    ");
    $stmt->execute([$matchId, $userId]);
    
    return $stmt->rowCount();
}
?>
