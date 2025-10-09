<?php
// PvP Live Battle Popup - Separate system for live matches
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_helpers.php';

$userId = $_SESSION['user_id'];
$matchId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

if (!$matchId) {
    http_response_code(400);
    exit('Match ID required');
}

// Get match data
$stmt = $db->prepare("
    SELECT m.*, u1.username as user1_name, u2.username as user2_name,
           b.league_id, l.name as league_name
    FROM pvp_matches m
    JOIN pvp_battles b ON m.battle_id = b.id
    JOIN pvp_leagues l ON b.league_id = l.id
    LEFT JOIN users u1 ON m.user1_id = u1.id
    LEFT JOIN users u2 ON m.user2_id = u2.id
    WHERE m.id = ? AND (m.user1_id = ? OR m.user2_id = ?)
");
$stmt->execute([$matchId, $userId, $userId]);
$match = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$match) {
    http_response_code(404);
    exit('Match not found');
}

// Determine opponent
$isUser1 = $match['user1_id'] == $userId;
$opponentName = $isUser1 ? $match['user2_name'] : $match['user1_name'];
$userScore = $isUser1 ? $match['user1_score'] : $match['user2_score'];
$opponentScore = $isUser1 ? $match['user2_score'] : $match['user1_score'];

// Calculam procentajele pentru bara de scor
$totalScore = $userScore + $opponentScore;
$userPercentage = $totalScore > 0 ? ($userScore / $totalScore) * 100 : 50;
$opponentPercentage = 100 - $userPercentage;

// Get latest chat messages
$stmt = $db->prepare("
    SELECT c.*, u.username
    FROM pvp_match_chat c
    JOIN users u ON c.user_id = u.id
    WHERE c.match_id = ?
    ORDER BY c.created_at DESC
    LIMIT 20
");
$stmt->execute([$matchId]);
$messages = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

// Mark messages as read for current user
markMatchChatAsRead($matchId, $userId);

// If AJAX request, return only popup content
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    ob_start();
    ?>
    <div class="popup-header">
        <div class="popup-title">Your Match - <?= htmlspecialchars($match['league_name']) ?></div>
        <button class="close-btn" onclick="closeLiveBattle()">&times;</button>
    </div>

    <div class="match-players">
        <div class="player">
            <img src="default-avatar.png" alt="Tu" class="player-avatar">
            <div class="player-name">Tu</div>
        </div>
        <div class="vs-divider">VS</div>
        <div class="player">
            <img src="default-avatar.png" alt="<?= htmlspecialchars($opponentName) ?>" class="player-avatar">
            <div class="player-name"><?= htmlspecialchars($opponentName) ?></div>
        </div>
    </div>

    <div class="score-bar-container">
        <div class="score-bar">
            <div class="score-left" style="width: <?= $userPercentage ?>%">
                <span class="score-value"><?= $userScore ?></span>
            </div>
            <div class="score-right" style="width: <?= $opponentPercentage ?>%">
                <span class="score-value"><?= $opponentScore ?></span>
            </div>
        </div>
    </div>

    <div class="match-info">
        <div class="match-message">The match is happening NOW!</div>
        <div class="match-timer" id="matchTimer">5:00</div>
        <button class="goto-battle-btn" onclick="goToBattle()">
            <i class="fas fa-play"></i> Watch Battle Live
        </button>
    </div>

    <div class="chat-section">
        <div class="chat-header">
            <i class="fas fa-comments"></i> Live Chat
            <span class="unread-badge" id="unreadCount" style="display: none;">0</span>
        </div>
        <div class="chat-messages" id="chatMessages">
            <?php foreach ($messages as $message): ?>
            <div class="chat-message <?= $message['user_id'] == $userId ? 'own' : '' ?>">
                <img src="default-avatar.png" alt="<?= htmlspecialchars($message['username']) ?>" class="message-avatar">
                <div class="message-bubble">
                    <?= htmlspecialchars($message['message']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="chat-input-container">
            <input type="text" id="chatInput" class="chat-input" 
                   placeholder="Write a message..." maxlength="200">
            <button id="chatSend" class="chat-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    <?php
    echo ob_get_clean();
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Battle - PvP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow: hidden;
        }

        .live-battle-popup {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 400px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            position: relative;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(-50px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .popup-header {
            background: rgba(0, 0, 0, 0.3);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .popup-title {
            font-size: 18px;
            font-weight: bold;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .match-players {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
        }

        .player {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .player-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid #ffd700;
            object-fit: cover;
            margin-bottom: 8px;
            background: #ddd;
        }

        .player-name {
            color: white;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
        }

        .vs-divider {
            font-size: 24px;
            font-weight: bold;
            color: #ffd700;
            margin: 0 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .score-bar-container {
            padding: 0 20px 20px;
        }

        .score-bar {
            background: rgba(0, 0, 0, 0.3);
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            position: relative;
        }

        .score-left {
            background: linear-gradient(90deg, #4caf50, #45a049);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            transition: width 0.5s ease;
        }

        .score-right {
            background: linear-gradient(90deg, #f44336, #d32f2f);
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding-left: 10px;
            transition: width 0.5s ease;
        }

        .score-value {
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .match-info {
            padding: 15px 20px;
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
        }

        .match-message {
            color: #ffd700;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }

        .match-timer {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }

        .goto-battle-btn {
            background: linear-gradient(45deg, #ff6b6b, #ff5252);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 auto;
        }

        .goto-battle-btn:hover {
            transform: scale(1.05);
        }

        .chat-section {
            background: rgba(0, 0, 0, 0.3);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .chat-header {
            padding: 10px 20px;
            background: rgba(0, 0, 0, 0.2);
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-messages {
            max-height: 150px;
            overflow-y: auto;
            padding: 10px 20px;
        }

        .chat-message {
            margin-bottom: 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .chat-message.own {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
        }

        .message-bubble {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 12px;
            border-radius: 15px;
            max-width: 70%;
            font-size: 13px;
            word-wrap: break-word;
        }

        .chat-message.own .message-bubble {
            background: rgba(76, 175, 80, 0.8);
        }

        .chat-input-container {
            padding: 15px 20px;
            display: flex;
            gap: 10px;
            background: rgba(0, 0, 0, 0.2);
        }

        .chat-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 14px;
        }

        .chat-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .chat-send-btn {
            background: #4caf50;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .chat-send-btn:hover {
            background: #45a049;
        }

        .unread-badge {
            background: #ff4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="live-battle-popup">
        <div class="popup-header">
            <div class="popup-title">Your Match - <?= htmlspecialchars($match['league_name']) ?></div>
            <button class="close-btn" onclick="closeLiveBattle()">&times;</button>
        </div>

        <div class="match-players">
            <div class="player">
                <img src="default-avatar.png" alt="Tu" class="player-avatar">
                <div class="player-name">Tu</div>
            </div>
            <div class="vs-divider">VS</div>
            <div class="player">
                <img src="default-avatar.png" alt="<?= htmlspecialchars($opponentName) ?>" class="player-avatar">
                <div class="player-name"><?= htmlspecialchars($opponentName) ?></div>
            </div>
        </div>

        <div class="score-bar-container">
            <div class="score-bar">
                <div class="score-left" style="width: <?= $userPercentage ?>%">
                    <span class="score-value"><?= $userScore ?></span>
                </div>
                <div class="score-right" style="width: <?= $opponentPercentage ?>%">
                    <span class="score-value"><?= $opponentScore ?></span>
                </div>
            </div>
        </div>

        <div class="match-info">
            <div class="match-message">The match is happening NOW!</div>
            <div class="match-timer" id="matchTimer">5:00</div>
            <button class="goto-battle-btn" onclick="goToBattle()">
                <i class="fas fa-play"></i> Watch Battle Live
            </button>
        </div>

        <div class="chat-section">
            <div class="chat-header">
                <i class="fas fa-comments"></i> Live Chat
                <span class="unread-badge" id="unreadCount" style="display: none;">0</span>
            </div>
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($messages as $message): ?>
                <div class="chat-message <?= $message['user_id'] == $userId ? 'own' : '' ?>">
                    <img src="default-avatar.png" alt="<?= htmlspecialchars($message['username']) ?>" class="message-avatar">
                    <div class="message-bubble">
                        <?= htmlspecialchars($message['message']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="chat-input-container">
                <input type="text" id="chatInput" class="chat-input" 
                       placeholder="Write a message..." maxlength="200">
                <button id="chatSend" class="chat-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        const matchId = <?= $matchId ?>;
        const userId = <?= $userId ?>;
        let timerInterval;
        let timeLeft = 300; // 5 minute

        // Timer countdown
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            document.getElementById('matchTimer').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                document.querySelector('.match-message').textContent = 'The match has ended!';
            }
            timeLeft--;
        }

        // Start timer
        timerInterval = setInterval(updateTimer, 1000);
        updateTimer();

        // Chat functionality
        document.getElementById('chatSend').addEventListener('click', sendMessage);
        document.getElementById('chatInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (!message) return;

            fetch('pvp_chat_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_message',
                    match_id: matchId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    addMessageToChat(data.message);
                }
            });
        }

        function addMessageToChat(message) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${message.user_id == userId ? 'own' : ''}`;
            messageDiv.innerHTML = `
                <img src="default-avatar.png" alt="${message.username}" class="message-avatar">
                <div class="message-bubble">${message.message}</div>
            `;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Close popup
        function closeLiveBattle() {
            if (window.opener) {
                window.close();
            } else {
                window.history.back();
            }
        }

        // Go to battle
        function goToBattle() {
            window.open('battle_simulation.php?match_id=' + matchId, '_blank');
        }

        // Auto-refresh chat every 3 seconds
        setInterval(() => {
            fetch('pvp_chat_api.php?action=get_messages&match_id=' + matchId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    const chatMessages = document.getElementById('chatMessages');
                    chatMessages.innerHTML = '';
                    data.messages.forEach(message => {
                        addMessageToChat(message);
                    });
                }
            });
        }, 3000);
    </script>
</body>
</html>
