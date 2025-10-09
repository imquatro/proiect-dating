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
        status ENUM('pending', 'active', 'completed', 'postponed') DEFAULT 'pending',
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (league_id) REFERENCES pvp_leagues(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
        last_reset_date DATE DEFAULT NULL,
        total_wins INT NOT NULL DEFAULT 0,
        total_losses INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (league_id) REFERENCES pvp_leagues(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
            ('Bronz', 1, '#CD7F32', 64),
            ('Argint', 2, '#C0C0C0', 64),
            ('Platina', 3, '#E5E4E2', 64)");
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
    
    // Vizite la ferma (2 puncte/vizita) - ultimele 7 zile
    $stmt = $db->prepare("SELECT COUNT(*) FROM farm_visits WHERE visited_user_id = ? AND visit_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$userId]);
    $visits = $stmt->fetchColumn();
    $score += $visits * 2;
    
    // Ajutoare primite (3 puncte/ajutor) - ultimele 7 zile
    $stmt = $db->prepare("SELECT COUNT(*) FROM help_records WHERE helped_user_id = ? AND helped_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$userId]);
    $helps = $stmt->fetchColumn();
    $score += $helps * 3;
    
    // Comentarii primite (2 puncte/comentariu) - ultimele 7 zile
    $stmt = $db->prepare("SELECT COUNT(*) FROM comments WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$userId]);
    $comments = $stmt->fetchColumn();
    $score += $comments * 2;
    
    // Interactiuni generale (1 punct) - mesaje trimise
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$userId]);
    $messages = $stmt->fetchColumn();
    $score += $messages * 1;
    
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
 * Aloca random 64 jucatori pentru un battle
 */
function allocatePlayers($battleId, $leagueId) {
    global $db;
    
    // Selectam 64 jucatori random din liga respectiva
    $stmt = $db->prepare("
        SELECT u.id, u.username 
        FROM users u
        JOIN user_league_status uls ON u.id = uls.user_id
        WHERE uls.league_id = ? AND u.is_active = 1
        ORDER BY RAND()
        LIMIT 64
    ");
    $stmt->execute([$leagueId]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculam si salvam scorul de popularitate pentru fiecare
    $stmt = $db->prepare("INSERT INTO pvp_participants (battle_id, user_id, popularity_score) VALUES (?, ?, ?)");
    foreach ($players as $player) {
        $score = calculatePopularityScore($player['id']);
        $stmt->execute([$battleId, $player['id'], $score]);
    }
    
    return count($players);
}

/**
 * Creeaza meciurile pentru prima runda (1/32 - 64 jucatori)
 */
function createFirstRoundMatches($battleId) {
    global $db;
    
    // Luam toti participantii sortati aleatoriu
    $stmt = $db->prepare("SELECT user_id, popularity_score FROM pvp_participants WHERE battle_id = ? ORDER BY RAND()");
    $stmt->execute([$battleId]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cream 32 meciuri (64 jucatori / 2)
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
        $winnerId = $match['user1_score'] > $match['user2_score'] ? $match['user1_id'] : $match['user2_id'];
        $loserId = $winnerId == $match['user1_id'] ? $match['user2_id'] : $match['user1_id'];
        
        $updateStmt->execute([$winnerId, $match['id']]);
        $eliminateStmt->execute([$roundNumber, $battleId, $loserId]);
        
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
 * Promoveaza top 4 jucatori (semifinalisti + finalisti) la liga superioara
 */
function promoteTopPlayers($battleId) {
    global $db;
    
    // Gasim semifinala si finala
    $stmt = $db->prepare("SELECT MAX(round_number) as final_round FROM pvp_matches WHERE battle_id = ?");
    $stmt->execute([$battleId]);
    $finalRound = $stmt->fetchColumn();
    
    $semifinalRound = $finalRound - 1;
    
    // Top 4: participantii din semifinale
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
    
    // Liga urmatoare (max 3)
    $nextLeagueId = min($currentLeagueId + 1, 3);
    
    if ($nextLeagueId > $currentLeagueId) {
        $updateStmt = $db->prepare("UPDATE user_league_status SET league_id = ? WHERE user_id = ?");
        foreach ($top4 as $userId) {
            $updateStmt->execute([$nextLeagueId, $userId]);
        }
    }
}

/**
 * Reset lunar - redistribuie toti jucatorii
 */
function monthlyLeagueReset() {
    global $db;
    $today = date('Y-m-01'); // Prima zi a lunii
    
    // Verificam daca s-a facut deja reset luna asta
    $stmt = $db->query("SELECT MAX(last_reset_date) FROM user_league_status");
    $lastReset = $stmt->fetchColumn();
    
    if ($lastReset && $lastReset >= $today) {
        return false; // Deja facut
    }
    
    // Resetam toti userii la liga bronz
    $db->exec("UPDATE user_league_status SET league_id = 1, last_reset_date = CURDATE()");
    
    return true;
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
