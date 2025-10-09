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
    case 'send_message':
        sendMessage($userId);
        break;
    
    case 'get_messages':
        $matchId = intval($_GET['match_id'] ?? 0);
        getMessages($matchId, $userId);
        break;
    
    case 'mark_read':
        $matchId = intval($_POST['match_id'] ?? 0);
        markAsRead($matchId, $userId);
        break;
    
    case 'get_unread_count':
        getUnreadCount($userId);
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

/**
 * Trimite un mesaj în chat
 */
function sendMessage($userId) {
    global $db;
    
    $matchId = intval($_POST['match_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!$matchId || !$message) {
        echo json_encode(['error' => 'Invalid data']);
        return;
    }
    
    // Validare mesaj
    if (strlen($message) > 100) {
        echo json_encode(['error' => 'Mesajul este prea lung (max 100 caractere)']);
        return;
    }
    
    // Verificăm dacă meciul există și userul participă
    $stmt = $db->prepare("SELECT * FROM pvp_matches WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
    $stmt->execute([$matchId, $userId, $userId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        echo json_encode(['error' => 'Nu participi la acest meci']);
        return;
    }
    
    if ($match['completed']) {
        echo json_encode(['error' => 'Meciul s-a încheiat']);
        return;
    }
    
    // Verificăm cooldown (5 secunde între mesaje)
    $stmt = $db->prepare("
        SELECT created_at FROM pvp_match_chat 
        WHERE match_id = ? AND user_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$matchId, $userId]);
    $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lastMessage) {
        $lastTime = strtotime($lastMessage['created_at']);
        $now = time();
        if (($now - $lastTime) < 5) {
            $remaining = 5 - ($now - $lastTime);
            echo json_encode(['error' => "Așteaptă $remaining secunde"]);
            return;
        }
    }
    
    // Verificăm limita de mesaje (max 30 per user per meci)
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_match_chat WHERE match_id = ? AND user_id = ?");
    $stmt->execute([$matchId, $userId]);
    $count = $stmt->fetchColumn();
    
    if ($count >= 30) {
        echo json_encode(['error' => 'Ai atins limita de mesaje pentru acest meci']);
        return;
    }
    
    // Salvăm mesajul
    $stmt = $db->prepare("INSERT INTO pvp_match_chat (match_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$matchId, $userId, $message]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $db->lastInsertId()
    ]);
}

/**
 * Obține mesajele dintr-un meci
 */
function getMessages($matchId, $userId) {
    global $db;
    
    if (!$matchId) {
        echo json_encode(['error' => 'Invalid match ID']);
        return;
    }
    
    // Verificăm dacă userul participă la meci
    $stmt = $db->prepare("SELECT * FROM pvp_matches WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
    $stmt->execute([$matchId, $userId, $userId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        echo json_encode(['error' => 'Nu participi la acest meci']);
        return;
    }
    
    // Luăm mesajele cu detalii despre useri
    $stmt = $db->prepare("
        SELECT c.*, u.username, u.photo, u.vip,
               CASE WHEN c.user_id = ? THEN 1 ELSE 0 END as is_own_message
        FROM pvp_match_chat c
        JOIN users u ON c.user_id = u.id
        WHERE c.match_id = ?
        ORDER BY c.created_at ASC
    ");
    $stmt->execute([$userId, $matchId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'messages' => $messages,
        'total' => count($messages)
    ]);
}

/**
 * Marchează mesajele ca citite
 */
function markAsRead($matchId, $userId) {
    global $db;
    
    if (!$matchId) {
        echo json_encode(['error' => 'Invalid match ID']);
        return;
    }
    
    $count = markMatchChatAsRead($matchId, $userId);
    
    echo json_encode([
        'success' => true,
        'marked' => $count
    ]);
}

/**
 * Obține numărul de mesaje necitite
 */
function getUnreadCount($userId) {
    global $db;
    
    $count = getUnreadMatchChatCount($userId);
    
    // Găsim și meciul pentru a returna detalii
    $status = getUserBattleStatus($userId);
    $matchId = null;
    $opponentId = null;
    
    if ($status && $status['current_match']) {
        $match = $status['current_match'];
        $matchId = $match['id'];
        $opponentId = $match['user1_id'] == $userId ? $match['user2_id'] : $match['user1_id'];
    }
    
    echo json_encode([
        'unread_count' => $count,
        'match_id' => $matchId,
        'opponent_id' => $opponentId
    ]);
}

