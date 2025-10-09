<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_helpers.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_battle_status':
        getBattleStatus($userId);
        break;
    
    case 'get_league_battles':
        $leagueId = intval($_GET['league_id'] ?? 1);
        getLeagueBattles($leagueId);
        break;
    
    case 'get_bracket':
        $battleId = intval($_GET['battle_id'] ?? 0);
        $roundNumber = intval($_GET['round'] ?? 0);
        getBracket($battleId, $roundNumber);
        break;
    
    case 'get_match_details':
        $matchId = intval($_GET['match_id'] ?? 0);
        getMatchDetails($matchId);
        break;
    
    case 'get_user_current_match':
        getUserCurrentMatch($userId);
        break;
    
    case 'get_all_leagues':
        getAllLeagues();
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Obține status-ul general al battle-ului pentru user
 */
function getBattleStatus($userId) {
    global $db;
    
    $status = getUserBattleStatus($userId);
    
    if (!$status) {
        echo json_encode([
            'has_active_battle' => false,
            'message' => 'Nu participi la niciun battle activ momentan'
        ]);
        return;
    }
    
    // Calculăm timpul rămas până la următoarea rundă
    $battle = $status['battle'];
    $currentRound = $battle['current_round'];
    $startDate = new DateTime($battle['start_date']);
    $now = new DateTime();
    
    // Fiecare rundă durează 1 zi
    $roundStartDate = clone $startDate;
    $roundStartDate->modify('+' . ($currentRound - 1) . ' days');
    $roundEndDate = clone $roundStartDate;
    $roundEndDate->modify('+1 day');
    
    $timeRemaining = $now->diff($roundEndDate);
    
    echo json_encode([
        'has_active_battle' => true,
        'battle_id' => $battle['id'],
        'league_id' => $status['league_id'],
        'current_round' => $currentRound,
        'status' => $battle['status'],
        'time_remaining' => [
            'days' => $timeRemaining->d,
            'hours' => $timeRemaining->h,
            'minutes' => $timeRemaining->i,
            'seconds' => $timeRemaining->s,
            'total_seconds' => $timeRemaining->days * 86400 + $timeRemaining->h * 3600 + $timeRemaining->i * 60 + $timeRemaining->s
        ],
        'current_match' => $status['current_match'],
        'is_eliminated' => $status['participation']['eliminated_in_round'] !== null
    ]);
}

/**
 * Obține toate battle-urile pentru o ligă
 */
function getLeagueBattles($leagueId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT b.*, l.name as league_name, l.color as league_color
        FROM pvp_battles b
        JOIN pvp_leagues l ON b.league_id = l.id
        WHERE b.league_id = ?
        ORDER BY b.start_date DESC
        LIMIT 10
    ");
    $stmt->execute([$leagueId]);
    $battles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['battles' => $battles]);
}

/**
 * Obține bracket-ul complet sau pentru o rundă specifică
 */
function getBracket($battleId, $roundNumber = 0) {
    global $db;
    
    if ($roundNumber > 0) {
        // Rundă specifică
        $stmt = $db->prepare("
            SELECT m.*, 
                   u1.username as user1_name, u1.photo as user1_photo, u1.vip as user1_vip,
                   u2.username as user2_name, u2.photo as user2_photo, u2.vip as user2_vip,
                   w.username as winner_name
            FROM pvp_matches m
            LEFT JOIN users u1 ON m.user1_id = u1.id
            LEFT JOIN users u2 ON m.user2_id = u2.id
            LEFT JOIN users w ON m.winner_id = w.id
            WHERE m.battle_id = ? AND m.round_number = ?
            ORDER BY m.id
        ");
        $stmt->execute([$battleId, $roundNumber]);
    } else {
        // Toate rundele
        $stmt = $db->prepare("
            SELECT m.*, 
                   u1.username as user1_name, u1.photo as user1_photo, u1.vip as user1_vip,
                   u2.username as user2_name, u2.photo as user2_photo, u2.vip as user2_vip,
                   w.username as winner_name
            FROM pvp_matches m
            LEFT JOIN users u1 ON m.user1_id = u1.id
            LEFT JOIN users u2 ON m.user2_id = u2.id
            LEFT JOIN users w ON m.winner_id = w.id
            WHERE m.battle_id = ?
            ORDER BY m.round_number, m.id
        ");
        $stmt->execute([$battleId]);
    }
    
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Grupăm pe runde
    $bracket = [];
    foreach ($matches as $match) {
        $round = $match['round_number'];
        if (!isset($bracket[$round])) {
            $bracket[$round] = [];
        }
        $bracket[$round][] = $match;
    }
    
    echo json_encode([
        'bracket' => $bracket,
        'total_rounds' => count($bracket)
    ]);
}

/**
 * Obține detalii complete pentru un meci specific
 */
function getMatchDetails($matchId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT m.*, 
               b.current_round, b.status as battle_status,
               u1.id as user1_id, u1.username as user1_name, u1.photo as user1_photo, u1.vip as user1_vip,
               u2.id as user2_id, u2.username as user2_name, u2.photo as user2_photo, u2.vip as user2_vip,
               w.username as winner_name
        FROM pvp_matches m
        JOIN pvp_battles b ON m.battle_id = b.id
        LEFT JOIN users u1 ON m.user1_id = u1.id
        LEFT JOIN users u2 ON m.user2_id = u2.id
        LEFT JOIN users w ON m.winner_id = w.id
        WHERE m.id = ?
    ");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        echo json_encode(['error' => 'Match not found']);
        return;
    }
    
    // Calculăm procentajele pentru bara de scor
    $totalScore = $match['user1_score'] + $match['user2_score'];
    $user1Percent = $totalScore > 0 ? ($match['user1_score'] / $totalScore) * 100 : 50;
    $user2Percent = $totalScore > 0 ? ($match['user2_score'] / $totalScore) * 100 : 50;
    
    $match['user1_percent'] = round($user1Percent, 2);
    $match['user2_percent'] = round($user2Percent, 2);
    
    echo json_encode(['match' => $match]);
}

/**
 * Obține meciul curent al userului (pentru popup)
 */
function getUserCurrentMatch($userId) {
    global $db;
    
    $status = getUserBattleStatus($userId);
    
    if (!$status || !$status['current_match']) {
        echo json_encode(['has_match' => false]);
        return;
    }
    
    $match = $status['current_match'];
    
    // Adăugăm detalii despre adversar
    $opponentId = $match['user1_id'] == $userId ? $match['user2_id'] : $match['user1_id'];
    
    $stmt = $db->prepare("SELECT id, username, photo, vip FROM users WHERE id = ?");
    $stmt->execute([$opponentId]);
    $opponent = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db->prepare("SELECT id, username, photo, vip FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Determinăm cine e user1 și user2
    $isUser1 = $match['user1_id'] == $userId;
    
    $userScore = $isUser1 ? $match['user1_score'] : $match['user2_score'];
    $opponentScore = $isUser1 ? $match['user2_score'] : $match['user1_score'];
    
    $totalScore = $userScore + $opponentScore;
    $userPercent = $totalScore > 0 ? ($userScore / $totalScore) * 100 : 50;
    $opponentPercent = $totalScore > 0 ? ($opponentScore / $totalScore) * 100 : 50;
    
    // Calculăm timp rămas
    $battle = $status['battle'];
    $startDate = new DateTime($battle['start_date']);
    $currentRound = $battle['current_round'];
    $now = new DateTime();
    
    $roundStartDate = clone $startDate;
    $roundStartDate->modify('+' . ($currentRound - 1) . ' days');
    $roundEndDate = clone $roundStartDate;
    $roundEndDate->modify('+1 day');
    
    $timeRemaining = $now->diff($roundEndDate);
    $totalSeconds = $timeRemaining->days * 86400 + $timeRemaining->h * 3600 + $timeRemaining->i * 60 + $timeRemaining->s;
    
    // Verificăm dacă suntem la 5 minute
    $isFinalMinutes = $totalSeconds <= 300 && $totalSeconds > 0;
    
    echo json_encode([
        'has_match' => true,
        'battle_id' => $battle['id'],
        'match_id' => $match['id'],
        'round_number' => $match['round_number'],
        'current_user' => $currentUser,
        'opponent' => $opponent,
        'user_score' => $userScore,
        'opponent_score' => $opponentScore,
        'user_percent' => round($userPercent, 2),
        'opponent_percent' => round($opponentPercent, 2),
        'time_remaining' => [
            'hours' => $timeRemaining->h,
            'minutes' => $timeRemaining->i,
            'seconds' => $timeRemaining->s,
            'total_seconds' => $totalSeconds
        ],
        'is_final_minutes' => $isFinalMinutes,
        'round_name' => getRoundName($match['round_number'])
    ]);
}

/**
 * Obține toate ligile
 */
function getAllLeagues() {
    global $db;
    
    $stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
    $leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['leagues' => $leagues]);
}

/**
 * Helper: Obține numele rundei
 */
function getRoundName($roundNumber) {
    $names = [
        1 => '1/32',
        2 => '1/16',
        3 => '1/8 (Optimi)',
        4 => '1/4 (Sferturi)',
        5 => '1/2 (Semifinală)',
        6 => 'Finală'
    ];
    
    return $names[$roundNumber] ?? "Rundă $roundNumber";
}

