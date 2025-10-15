<?php
$activePage = 'pvp';
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_server_heartbeat.php';

$userId = $_SESSION['user_id'];

// Verifică și curăță automat battle-urile întrerupte dacă serverul nu rulează
checkAndCleanupPvpBattles();

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

// Check loop status
$stmt = $db->query("SELECT setting_value FROM pvp_settings WHERE setting_name = 'loop_enabled'");
$loopEnabled = $stmt->fetchColumn();
$loopEnabled = ($loopEnabled === '1' || $loopEnabled === 1);

// Get active battle for user's league
$stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
$stmt->execute([$userLeagueId]);
$activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if final display time has expired and loop is disabled
if ($activeBattle && $activeBattle['status'] === 'displaying_final' && !$loopEnabled) {
    // Get timer settings
    $stmt = $db->query("SELECT setting_value FROM pvp_settings WHERE setting_name = 'final_display_minutes'");
    $finalDisplayMinutes = $stmt->fetchColumn();
    $finalDisplayMinutes = $finalDisplayMinutes ? (int)$finalDisplayMinutes : 5;
    
    // Check if time expired
    $completedAt = new DateTime($activeBattle['completed_at']);
    $now = new DateTime();
    $minutesSince = floor(($now->getTimestamp() - $completedAt->getTimestamp()) / 60);
    
    if ($minutesSince >= $finalDisplayMinutes) {
        // Time expired and loop disabled - treat as no active battle
        $activeBattle = null;
    }
}

// Check if user is participant in active battle
$userIsParticipant = false;
$userCurrentRound = 0;
$userIsEliminated = false;
$userEliminationRound = 0;

if ($activeBattle) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM pvp_matches WHERE battle_id = ? AND (user1_id = ? OR user2_id = ?)");
    $stmt->execute([$activeBattle['id'], $userId, $userId]);
    $userIsParticipant = $stmt->fetchColumn() > 0;
    
    if ($userIsParticipant) {
        $userCurrentRound = $activeBattle['current_round'];
    } else {
        // Check if user was eliminated in this battle
        $stmt = $db->prepare("SELECT round_number FROM pvp_matches WHERE battle_id = ? AND (user1_id = ? OR user2_id = ?) AND completed = 1 ORDER BY round_number DESC LIMIT 1");
        $stmt->execute([$activeBattle['id'], $userId, $userId]);
        $eliminationRound = $stmt->fetchColumn();
        
        if ($eliminationRound) {
            $userIsEliminated = true;
            $userEliminationRound = $eliminationRound;
        }
    }
}

ob_start();
?>
<div class="pvp-container">
    <div id="pvpPanel" class="pvp-panel">
        <!-- Header with Timer and Status -->
        <div class="pvp-header">
            <div class="pvp-status-league-container">
                <div id="pvpStatus" class="pvp-status">
                    <?php if ($userIsEliminated): ?>
                        ❌ You were eliminated in Round <?= $userEliminationRound ?>
                    <?php elseif ($userIsParticipant): ?>
                        🎮 You are participating in this battle!
                    <?php elseif ($activeBattle): ?>
                        👀 Watching battle in progress
                    <?php else: ?>
                        ⏳ Waiting for next battle
                    <?php endif; ?>
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
        </div>
        
        <!-- Scoring Info Panel -->
        <div class="pvp-scoring-info">
            <button class="scoring-info-toggle" onclick="toggleScoringInfo()">
                <i class="fas fa-info-circle"></i> How Points Are Calculated
            </button>
            <div class="scoring-info-content" id="scoringInfoContent" style="display: none;">
                <h3><i class="fas fa-trophy"></i> Popularity Score System</h3>
                <div class="scoring-rules">
                    <div class="scoring-rule">
                        <div class="rule-icon">🤝</div>
                        <div class="rule-text">
                            <strong>Help Given:</strong> 1 point per 100 helps
                        </div>
                    </div>
                    <div class="scoring-rule">
                        <div class="rule-icon">💝</div>
                        <div class="rule-text">
                            <strong>Help Received:</strong> 1 point per 300 helps
                        </div>
                    </div>
                    <div class="scoring-rule">
                        <div class="rule-icon">🌟</div>
                        <div class="rule-text">
                            <strong>Diversity Bonus:</strong> 1 point per 10 unique users helped
                        </div>
                    </div>
                    <div class="scoring-rule">
                        <div class="rule-icon">👥</div>
                        <div class="rule-text">
                            <strong>Trusted Helper Bonus:</strong> 1 point per 5 users who helped you 10+ times
                        </div>
                    </div>
                </div>
                <div class="scoring-note">
                    <i class="fas fa-lightbulb"></i> <strong>Tip:</strong> Be active, help others, and build friendships to increase your popularity score!
                </div>
            </div>
        </div>
        
        <!-- Global Timer - positioned between scoring info and league tabs -->
        <div class="pvp-global-timer-wrapper">
            <div class="pvp-group-timer" id="pvpGlobalTimer">
                <div class="group-timer-title">
                    <i class="fas fa-trophy"></i> ROUND TIME REMAINING
                </div>
                <div class="group-timer-display">
                    <div class="timer-block">
                        <span class="timer-value timer-minutes">--</span>
                        <span class="timer-label">MIN</span>
                    </div>
                    <span class="timer-separator">:</span>
                    <div class="timer-block">
                        <span class="timer-value timer-seconds">--</span>
                        <span class="timer-label">SEC</span>
                    </div>
                </div>
                <div class="group-timer-status" id="timerRoundLabel">Select a league to view tournament</div>
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
                
                <!-- Sub-tabs pentru Runde (Sticky) -->
                <div class="pvp-round-tabs pvp-sticky-round-tabs" id="roundTabs-<?= $league['id'] ?>">
                    <button class="round-btn" data-round="1">1/32</button>
                    <button class="round-btn" data-round="2">1/16</button>
                    <button class="round-btn" data-round="3">1/8</button>
                    <button class="round-btn" data-round="4">1/4 (Semifinal)</button>
                    <button class="round-btn" data-round="5">Final</button>
                </div>
                
                <!-- Bracket Container -->
                <div class="bracket-container" id="bracket-<?= $league['id'] ?>">
                    <?php
                    // Get battle for this league - only if truly active (not interrupted)
                    $stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 AND status = 'active' ORDER BY id DESC LIMIT 1");
                    $stmt->execute([$league['id']]);
                    $leagueBattle = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($leagueBattle && $leagueBattle['is_active'] == 1) {
                        // Show rounds with matches - only for current and completed rounds
                        for ($round = 1; $round <= 5; $round++) {
                            // Only show matches for rounds that have started (current or completed)
                            if ($round <= $leagueBattle['current_round']) {
                            $stmt = $db->prepare("
                                SELECT m.*, 
                                       u1.id as u1_id,
                                       u1.username as user1_name, 
                                       CASE 
                                           WHEN u1.gallery IS NOT NULL AND u1.gallery != '' THEN CONCAT('uploads/', u1.id, '/', TRIM(SUBSTRING_INDEX(u1.gallery, ',', 1)))
                                           ELSE 'default-avatar.png'
                                       END as user1_photo,
                                       u1.vip as user1_vip,
                                       u2.id as u2_id,
                                       u2.username as user2_name, 
                                       CASE 
                                           WHEN u2.gallery IS NOT NULL AND u2.gallery != '' THEN CONCAT('uploads/', u2.id, '/', TRIM(SUBSTRING_INDEX(u2.gallery, ',', 1)))
                                           ELSE 'default-avatar.png'
                                       END as user2_photo,
                                       u2.vip as u2_vip
                                FROM pvp_matches m
                                LEFT JOIN users u1 ON m.user1_id = u1.id
                                LEFT JOIN users u2 ON m.user2_id = u2.id
                                WHERE m.battle_id = ? AND m.round_number = ?
                                ORDER BY m.id
                            ");
                            $stmt->execute([$leagueBattle['id'], $round]);
                            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (!empty($matches)) {
                                // Sort matches: user's matches first, then others
                                usort($matches, function($a, $b) use ($userId) {
                                    $aUserMatch = ($a['user1_id'] == $userId || $a['user2_id'] == $userId);
                                    $bUserMatch = ($b['user1_id'] == $userId || $b['user2_id'] == $userId);
                                    
                                    if ($aUserMatch && !$bUserMatch) return -1;
                                    if (!$aUserMatch && $bUserMatch) return 1;
                                    return 0;
                                });
                                $roundName = '';
                                switch($round) {
                                    case 1: $roundName = '1/32 Finals'; break;
                                    case 2: $roundName = '1/16 Finals'; break;
                                    case 3: $roundName = '1/8 Finals'; break;
                                    case 4: $roundName = '1/4 Finals (Semifinal)'; break;
                                    case 5: $roundName = 'Final'; break;
                                }
                                
                                echo '<div class="bracket-round" data-round="' . $round . '">';
                                // Removed round title per request
                                echo '<div class="bracket-matches">';
                                
                                foreach ($matches as $match) {
                                    // Determine winner/loser classes with visual effects
                                    $user1Class = '';
                                    $user2Class = '';
                                    $vsClass = '';
                                    $matchClass = '';
                                    
                                    if ($match['completed']) {
                                        $matchClass = ' completed';
                                        // Use winner_id to determine winner/loser (handles ties correctly)
                                        if (!empty($match['winner_id'])) {
                                            if ($match['winner_id'] == $match['user1_id']) {
                                                $user1Class = ' winner';
                                                $user2Class = ' loser';
                                            } else {
                                                $user1Class = ' loser';
                                                $user2Class = ' winner';
                                            }
                                        }
                                    } else {
                                        $vsClass = ' live';
                                        $matchClass = ' live';
                                    }
                                    
                                    // Check if user is in this match
                                    $userInMatch = ($match['user1_id'] == $userId || $match['user2_id'] == $userId);
                                    if ($userInMatch) {
                                        $matchClass .= ' user-match';
                                    }
                                    
                                    // Calculate percentage for diagonal line
                                    $totalScore = $match['user1_score'] + $match['user2_score'];
                                    $user1Percent = $totalScore > 0 ? ($match['user1_score'] / $totalScore) * 100 : 50;
                                    $safePercent = max(10, min(90, $user1Percent));
                                    
                                    echo '<div class="match-card' . $matchClass . '">';
                                    echo '<div class="card-background">';
                                    echo '<div class="color-section red-section" style="width: ' . $safePercent . '%;"></div>';
                                    echo '<div class="color-section blue-section" style="width: ' . (100 - $safePercent) . '%; left: ' . $safePercent . '%;"></div>';
                                    echo '</div>';
                                    echo '<div class="match-players">';
                                    
                                    // Player 1
                                    echo '<div class="match-player' . $user1Class . '">';
                                    echo '<img src="' . $match['user1_photo'] . '" class="match-player-avatar" alt="' . $match['user1_name'] . '">';
                                    echo '<div class="match-player-name' . (!empty($match['user1_vip']) ? ' vip' : ' xp-fade') . '">' . $match['user1_name'] . '</div>';
                                    echo '<div class="match-player-score">' . $match['user1_score'] . '</div>';
                                    if (!empty($match['user1_vip'])) echo '<div class="vip-badge">👑 VIP</div>';
                                    echo '</div>';
                                    
                                    echo '<div class="match-vs' . $vsClass . '">VS</div>';
                                    
                                    // Player 2
                                    echo '<div class="match-player' . $user2Class . '">';
                                    echo '<img src="' . $match['user2_photo'] . '" class="match-player-avatar" alt="' . $match['user2_name'] . '">';
                                    echo '<div class="match-player-name' . (!empty($match['user2_vip']) ? ' vip' : ' xp-fade') . '">' . $match['user2_name'] . '</div>';
                                    echo '<div class="match-player-score">' . $match['user2_score'] . '</div>';
                                    if (!empty($match['user2_vip'])) echo '<div class="vip-badge">👑 VIP</div>';
                                    echo '</div>';
                                    
                                    echo '</div>';
                                    echo '</div>';
                                }
                                
                                echo '</div>';
                                echo '</div>';
                            } else {
                                // No matches for this round yet
                                echo '<div class="no-battle-message">';
                                echo '<i class="fas fa-info-circle"></i>';
                                echo '<p>No matches found for this round</p>';
                                echo '<p class="next-battle-info">Matches will appear when the round begins!</p>';
                                echo '</div>';
                            }
                            }
                        }
                    } else {
                        // No active battle for this league
                        echo '<div class="no-battle-message">';
                        echo '<i class="fas fa-info-circle"></i>';
                        echo '<p>No active battle for this league at the moment.</p>';
                        echo '<p class="next-battle-info">Battle will start automatically when there are enough players!</p>';
                        echo '</div>';
                    }
                    ?>
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
     data-active-battle-id="<?= $activeBattle ? $activeBattle['id'] : '' ?>"
     data-user-is-participant="<?= $userIsParticipant ? '1' : '0' ?>"
     data-user-current-round="<?= $userCurrentRound ?>"
     data-user-is-eliminated="<?= $userIsEliminated ? '1' : '0' ?>"
     data-user-elimination-round="<?= $userEliminationRound ?>">
</div>

<script>
// Complete bracket system with all rounds
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;
            
            // Remove active from all tabs
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active to clicked tab
            btn.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // Round tab switching with content display
    const roundButtons = document.querySelectorAll('.round-btn');
    roundButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const parentTabs = btn.closest('.pvp-round-tabs');
            const allRoundBtns = parentTabs.querySelectorAll('.round-btn');
            const targetRound = btn.dataset.round;
            
            // Remove active from all round buttons in this league
            allRoundBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            // Show/hide bracket rounds
            const bracketContainer = parentTabs.nextElementSibling;
            const allRounds = bracketContainer.querySelectorAll('.bracket-round');
            
            allRounds.forEach(round => {
                if (round.dataset.round === targetRound) {
                    round.style.display = 'block';
                } else {
                    round.style.display = 'none';
                }
            });
            
            // Scroll to top of bracket container for better visibility
            bracketContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // Scoring info toggle
    window.toggleScoringInfo = function() {
        const content = document.getElementById('scoringInfoContent');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    };
    
    // Auto-highlight current round if user is participant
    const userIsParticipant = document.getElementById('pvpData').dataset.userIsParticipant === '1';
    const userCurrentRound = parseInt(document.getElementById('pvpData').dataset.userCurrentRound);
    
    console.log('PVP Navigation Debug:', {
        userIsParticipant: userIsParticipant,
        userCurrentRound: userCurrentRound
    });
    
    let targetRound = 1; // Default to 1/32
    
    if (userIsParticipant && userCurrentRound > 0) {
        // User is participant - show their current round
        targetRound = userCurrentRound;
        console.log('User is participant, showing round:', targetRound);
    } else {
        console.log('User not participant or no current round, defaulting to round 1');
    }
    
    // Find and activate the target round button
    const activeTab = document.querySelector('.tab-content.active');
    const roundBtn = activeTab.querySelector(`[data-round="${targetRound}"]`);
    if (roundBtn) {
        console.log('Activating round button:', targetRound);
        roundBtn.classList.add('active');
        roundBtn.click(); // Trigger the click to show the round
    } else {
        console.log('Round button not found for round:', targetRound);
    }
    
    // Initialize all rounds to be hidden except the active one
    const allBracketRounds = document.querySelectorAll('.bracket-round');
    allBracketRounds.forEach(round => {
        round.style.display = 'none';
    });
    
    // Show the active round
    const activeRoundBtn = document.querySelector('.round-btn.active');
    if (activeRoundBtn) {
        const targetRound = activeRoundBtn.dataset.round;
        const activeTab = document.querySelector('.tab-content.active');
        const targetRoundEl = activeTab.querySelector(`[data-round="${targetRound}"]`);
        if (targetRoundEl) {
            targetRoundEl.style.display = 'block';
        }
    }
});
</script>

<style>
/* Additional styles for complete bracket system */
.bracket-round {
    margin-bottom: 30px;
}

.bracket-round-title {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 20px;
    color: #333;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    border-radius: 10px;
}

.bracket-matches {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.match-card.user-match {
    border: 3px solid #ff6b6b;
    box-shadow: 0 0 20px rgba(255, 107, 107, 0.5);
    background: linear-gradient(135deg, rgba(255, 0, 0, 0.95), rgba(0, 0, 255, 0.95));
    transform: scale(1.02);
    z-index: 10;
    position: relative;
}

.match-card.user-match .match-header {
    background: #ff6b6b;
    color: white;
    font-weight: bold;
    font-size: 18px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.match-card.user-match .match-player-avatar {
    border: 3px solid #ff6b6b;
    box-shadow: 0 0 15px rgba(255, 107, 107, 0.6);
}

.match-player.winner .match-player-avatar {
    border-color: #f6cf49;
    box-shadow: 0 0 20px rgba(76, 175, 80, 0.8);
    animation: pulseGoldenGlow 2s ease-in-out infinite;
}

.match-player.loser .match-player-avatar {
    border-color: #2c2c2c;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.9), 0 0 30px rgba(0, 0, 0, 0.6);
    filter: grayscale(20%);
}

.match-vs.live {
    animation: pulsateVS 1.5s ease-in-out infinite;
    color: #ff4444;
    text-shadow: 0 0 10px rgba(255, 68, 68, 0.8);
}

@keyframes pulseGoldenGlow {
    0%, 100% { box-shadow: 0 0 20px rgba(76, 175, 80, 0.8); }
    50% { box-shadow: 0 0 30px rgba(76, 175, 80, 1), 0 0 40px rgba(76, 175, 80, 0.6); }
}

@keyframes pulsateVS {
    0%, 100% { 
        transform: scale(1);
        opacity: 1;
    }
    50% { 
        transform: scale(1.1);
        opacity: 0.8;
    }
}

.round-btn.active {
    background: #f6cf49;
    color: white;
    transform: scale(1.05);
}

.round-btn {
    transition: all 0.3s ease;
}

.round-btn:hover {
    background: #e6c042;
    color: white;
    transform: scale(1.02);
}

/* Card background with diagonal colors */
.match-card {
    position: relative;
    overflow: hidden;
}

.card-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.color-section {
    position: absolute;
    top: 0;
    height: 100%;
}

.red-section {
    left: 0;
    background: rgba(255, 0, 0, 0.4);
}

.blue-section {
    background: rgba(0, 0, 255, 0.4);
}


/* Ensure content is above background */
.match-players {
    position: relative;
    z-index: 2;
}

.match-player {
    position: relative;
    z-index: 2;
}

.match-vs {
    position: relative;
    z-index: 2;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'PvP Battles';
$pageCss = 'assets_css/pvp.css?v=' . time();
$extraCss = ['assets_css/pvp-chat.css'];
$extraJs = '<script src="assets_js/pvp.js?v=' . rand(1000000, 9999999) . '"></script>';
include 'template.php';
?>
