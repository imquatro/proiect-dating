<?php
$activePage = 'pvp';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

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
                
                <!-- Bracket Container -->
                <div class="bracket-container" id="bracket-<?= $league['id'] ?>">
                    <?php
                    // Get matches for current round directly with PHP
                    if ($activeBattle && $activeBattle['league_id'] == $league['id']) {
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
                        $stmt->execute([$activeBattle['id'], $activeBattle['current_round']]);
                        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($matches)) {
                            echo '<div class="bracket-round">';
                            echo '<div class="bracket-round-title">Round ' . $activeBattle['current_round'] . '</div>';
                            echo '<div class="bracket-matches">';
                            
                            foreach ($matches as $match) {
                                // Determine winner/loser classes
                                $user1Class = '';
                                $user2Class = '';
                                $vsClass = '';
                                
                                if ($match['completed']) {
                                    if ($match['user1_score'] > $match['user2_score']) {
                                        $user1Class = ' winner';
                                        $user2Class = ' loser';
                                    } elseif ($match['user2_score'] > $match['user1_score']) {
                                        $user1Class = ' loser';
                                        $user2Class = ' winner';
                                    }
                                } else {
                                    $vsClass = ' live';
                                }
                                
                                echo '<div class="match-card' . ($match['completed'] ? ' completed' : ' live') . '">';
                                echo '<div class="match-header">Match #' . $match['id'] . '</div>';
                                echo '<div class="match-players">';
                                
                                // Player 1
                                echo '<div class="match-player' . $user1Class . '">';
                                echo '<img src="' . $match['user1_photo'] . '" class="match-player-avatar" alt="' . $match['user1_name'] . '">';
                                echo '<div class="match-player-name' . (!empty($match['user1_vip']) ? ' vip' : '') . '">' . $match['user1_name'] . '</div>';
                                echo '<div class="match-player-score">' . $match['user1_score'] . '</div>';
                                if (!empty($match['user1_vip'])) echo '<div class="vip-badge">👑 VIP</div>';
                                echo '</div>';
                                
                                echo '<div class="match-vs' . $vsClass . '">VS</div>';
                                
                                // Player 2
                                echo '<div class="match-player' . $user2Class . '">';
                                echo '<img src="' . $match['user2_photo'] . '" class="match-player-avatar" alt="' . $match['user2_name'] . '">';
                                echo '<div class="match-player-name' . (!empty($match['user2_vip']) ? ' vip' : '') . '">' . $match['user2_name'] . '</div>';
                                echo '<div class="match-player-score">' . $match['user2_score'] . '</div>';
                                if (!empty($match['user2_vip'])) echo '<div class="vip-badge">👑 VIP</div>';
                                echo '</div>';
                                
                                echo '</div>';
                                echo '<div class="match-status">' . ($match['completed'] ? 'Finished' : 'Active') . '</div>';
                                echo '</div>';
                            }
                            
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- No Battle Message - Only show when no battle exists -->
                <div class="no-battle-message" id="noBattle-<?= $league['id'] ?>" style="<?= $activeBattle ? 'display: none;' : '' ?>">
                    <i class="fas fa-info-circle"></i>
                    <p>No active battle for this league at the moment.</p>
                    <p class="next-battle-info">Battle will start automatically when there are enough players!</p>
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

<script>
// Simple tab switching without API calls
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
    
    // Round tab switching
    const roundButtons = document.querySelectorAll('.round-btn');
    roundButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const parentTabs = btn.closest('.pvp-round-tabs');
            const allRoundBtns = parentTabs.querySelectorAll('.round-btn');
            
            allRoundBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
    
    // Scoring info toggle
    window.toggleScoringInfo = function() {
        const content = document.getElementById('scoringInfoContent');
        content.style.display = content.style.display === 'none' ? 'block' : 'none';
    };
    
    // Auto-highlight current round
    const activeBattle = document.querySelector('[data-has-active-battle="1"]');
    if (activeBattle) {
        const currentRound = <?= $activeBattle ? $activeBattle['current_round'] : 1 ?>;
        const roundBtn = document.querySelector(`[data-round="${currentRound}"]`);
        if (roundBtn) {
            roundBtn.classList.add('active');
        }
    }
});
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'PvP Battles';
$pageCss = 'assets_css/pvp.css';
$extraCss = ['assets_css/pvp-chat.css'];
include 'template.php';
?>
