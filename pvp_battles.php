<?php
$activePage = 'pvp';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_helpers.php';

$userId = $_SESSION['user_id'];

// Check user's league
$stmt = $db->prepare("SELECT league_id FROM user_league_status WHERE user_id = ?");
$stmt->execute([$userId]);
$userLeagueId = $stmt->fetchColumn();

if (!$userLeagueId) {
    // If not in any league, put in bronze
    $db->prepare("INSERT INTO user_league_status (user_id, league_id) VALUES (?, 1)")->execute([$userId]);
    $userLeagueId = 1;
}

// Get all leagues
$stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
$leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active battle for user's league
$stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
$stmt->execute([$userLeagueId]);
$activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="pvp-container">
    <div id="pvpPanel" class="pvp-panel">
        <!-- Header with Timer and Status -->
        <div class="pvp-header">
            <div class="pvp-timer-container">
                <div id="pvpTimer" class="pvp-timer">
                    <i class="fas fa-clock"></i> 
                    <span id="timerText">Loading...</span>
                </div>
                <div id="pvpStatus" class="pvp-status">Loading...</div>
            </div>
            <div class="pvp-user-league">
                <span class="league-label">Your League:</span>
                <span id="userLeagueName" class="league-name" data-league-id="<?= $userLeagueId ?>">
                    <?php 
                    $userLeague = array_filter($leagues, fn($l) => $l['id'] == $userLeagueId);
                    $userLeague = reset($userLeague);
                    echo $userLeague ? $userLeague['name'] : 'Bronze';
                    ?>
                </span>
            </div>
        </div>
        
        <!-- Tabs Ligi -->
        <div class="pvp-tabs">
            <?php foreach ($leagues as $index => $league): ?>
            <button class="tab-btn <?= $league['id'] == $userLeagueId ? 'active' : '' ?>" 
                    data-tab="league-<?= $league['id'] ?>"
                    data-league-id="<?= $league['id'] ?>">
                <?php if ($league['level'] == 1): ?>
                    🥉
                <?php elseif ($league['level'] == 2): ?>
                    🥈
                <?php else: ?>
                    🥇
                <?php endif; ?>
                <?= htmlspecialchars($league['name']) ?>
            </button>
            <?php endforeach; ?>
        </div>
        
        <!-- Continut Tabs -->
        <div class="pvp-tab-content">
            <?php foreach ($leagues as $index => $league): ?>
            <div class="tab-content <?= $league['id'] == $userLeagueId ? 'active' : '' ?>" 
                 id="league-<?= $league['id'] ?>"
                 data-league-id="<?= $league['id'] ?>">
                
                <!-- Sub-tabs pentru Runde -->
                <div class="pvp-round-tabs" id="roundTabs-<?= $league['id'] ?>">
                    <button class="round-btn" data-round="1">1/32</button>
                    <button class="round-btn" data-round="2">1/16</button>
                    <button class="round-btn" data-round="3">1/8</button>
                    <button class="round-btn" data-round="4">1/4 (Semifinal)</button>
                    <button class="round-btn" data-round="5">Final</button>
                </div>
                
                <!-- Loading -->
                <div class="bracket-loading" id="loading-<?= $league['id'] ?>">
                    <i class="fas fa-spinner fa-spin"></i> Loading bracket...
                </div>
                
                <!-- Bracket Container -->
                <div class="bracket-container" id="bracket-<?= $league['id'] ?>" style="display: none;">
                    <!-- Generat dinamic prin JS -->
                </div>
                
                <!-- No Battle Message -->
                <div class="no-battle-message" id="noBattle-<?= $league['id'] ?>" style="display: none;">
                    <i class="fas fa-info-circle"></i>
                    <p>No active battle for this league at the moment.</p>
                    <p class="next-battle-info">Next battle will start soon!</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Hidden data pentru JS -->
<div id="pvpData" style="display: none;" 
     data-user-id="<?= $userId ?>" 
     data-user-league-id="<?= $userLeagueId ?>"
     data-has-active-battle="<?= $activeBattle ? '1' : '0' ?>"
     data-active-battle-id="<?= $activeBattle ? $activeBattle['id'] : '' ?>">
</div>

<!-- Live Battle Popup (hidden by default) -->
<div id="liveBattlePopup" class="live-battle-overlay" style="display: none;">
    <div class="live-battle-popup">
        <div class="popup-header">
            <div class="popup-title">Your Match</div>
            <button class="close-btn" onclick="closeLiveBattle()">&times;</button>
        </div>
        <div class="match-players">
            <div class="player">
                <img src="default-avatar.png" alt="You" class="player-avatar">
                <div class="player-name">You</div>
            </div>
            <div class="vs-divider">VS</div>
            <div class="player">
                <img src="default-avatar.png" alt="Opponent" class="player-avatar">
                <div class="player-name">Opponent</div>
            </div>
        </div>
        <div class="score-bar-container">
            <div class="score-bar">
                <div class="score-left" style="width: 50%">
                    <span class="score-value">0</span>
                </div>
                <div class="score-right" style="width: 50%">
                    <span class="score-value">0</span>
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
            <div class="chat-messages" id="chatMessages"></div>
            <div class="chat-input-container">
                <input type="text" id="chatInput" class="chat-input" placeholder="Write a message..." maxlength="200">
                <button id="chatSend" class="chat-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-popup for live matches with 5 minutes remaining
document.addEventListener('DOMContentLoaded', function() {
    const userId = <?= $userId ?>;
    const userLeagueId = <?= $userLeagueId ?>;
    
    // Check if there's an active match with 5 minutes remaining
    function checkForLiveMatch() {
        fetch('pvp_api.php?action=check_live_match&user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.has_live_match && data.time_remaining <= 300) { // 5 minutes = 300 seconds
                    // Show popup in site
                    showLiveBattlePopup(data.match_id);
                }
            })
            .catch(error => console.error('Error checking live match:', error));
    }
    
    // Show Live Battle popup in site
    function showLiveBattlePopup(matchId) {
        const popup = document.getElementById('liveBattlePopup');
        if (popup) {
            popup.style.display = 'flex';
            loadMatchData(matchId);
        }
    }
    
    // Load match data in popup
    function loadMatchData(matchId) {
        fetch(`pvp_live_battle.php?match_id=${matchId}&ajax=1`)
            .then(response => response.text())
            .then(html => {
                // Extract only popup content from HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const popupContent = doc.querySelector('.live-battle-popup');
                
                if (popupContent) {
                    const currentPopup = document.querySelector('.live-battle-popup');
                    currentPopup.innerHTML = popupContent.innerHTML;
                    initializeLiveBattle();
                }
            })
            .catch(error => console.error('Error loading match data:', error));
    }
    
    // Initialize Live Battle features
    function initializeLiveBattle() {
        const matchId = new URLSearchParams(window.location.search).get('match_id') || 1;
        let timeLeft = 300; // 5 minutes
        
        // Timer countdown
        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const timerEl = document.getElementById('matchTimer');
            if (timerEl) {
                timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }
            
            if (timeLeft <= 0) {
                const messageEl = document.querySelector('.match-message');
                if (messageEl) {
                    messageEl.textContent = 'The match has ended!';
                }
            }
            timeLeft--;
        }
        
        // Start timer
        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
        
        // Chat functionality
        const chatSend = document.getElementById('chatSend');
        const chatInput = document.getElementById('chatInput');
        
        if (chatSend && chatInput) {
            chatSend.addEventListener('click', sendMessage);
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        }
        
        function sendMessage() {
            const message = chatInput.value.trim();
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
                    chatInput.value = '';
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
    }
    
    // Închide popup-ul
    window.closeLiveBattle = function() {
        const popup = document.getElementById('liveBattlePopup');
        if (popup) {
            popup.style.display = 'none';
        }
    };
    
    // Go to battle
    window.goToBattle = function() {
        alert('Battle Live functionality will be implemented!');
    };
    
    // Check every 30 seconds
    setInterval(checkForLiveMatch, 30000);
    
    // Check immediately on load
    checkForLiveMatch();
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'PvP Battles';
$pageCss = 'assets_css/pvp.css';
$extraCss = ['assets_css/pvp-chat.css'];
$extraJs = ['assets_js/pvp.js'];
include 'template.php';
?>
