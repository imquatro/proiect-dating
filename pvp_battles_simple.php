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

// Get active battle for user's league
$stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
$stmt->execute([$userLeagueId]);
$activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="pvp-container">
    <div class="pvp-panel">
        <!-- Header -->
        <div class="pvp-header">
            <h1>🎮 PVP Battles</h1>
            <div class="pvp-user-league">
                <span class="league-label">Your League:</span>
                <span class="league-name">Bronze</span>
            </div>
        </div>
        
        <!-- Battle Info -->
        <?php if ($activeBattle): ?>
        <div class="battle-info">
            <h2>✅ Active Battle Found</h2>
            <p><strong>Battle ID:</strong> <?= $activeBattle['id'] ?></p>
            <p><strong>Current Round:</strong> <?= $activeBattle['current_round'] ?></p>
            <p><strong>Status:</strong> <?= $activeBattle['status'] ?></p>
        </div>
        
        <!-- Matches -->
        <div class="matches-container">
            <h3>🎮 Current Round Matches:</h3>
            <div class="matches-grid">
                <?php
                // Get matches for current round
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
                
                foreach ($matches as $match) {
                    echo '<div class="match-card">';
                    echo '<div class="match-header">Match #' . $match['id'] . '</div>';
                    echo '<div class="match-players">';
                    
                    // Player 1
                    echo '<div class="match-player">';
                    echo '<img src="' . $match['user1_photo'] . '" class="match-player-avatar" alt="' . $match['user1_name'] . '">';
                    echo '<div class="match-player-name' . ($match['user1_vip'] ? ' vip' : '') . '">' . $match['user1_name'] . '</div>';
                    echo '<div class="match-player-score">Score: ' . $match['user1_score'] . '</div>';
                    if ($match['user1_vip']) echo '<div class="vip-badge">👑 VIP</div>';
                    echo '</div>';
                    
                    echo '<div class="match-vs">VS</div>';
                    
                    // Player 2
                    echo '<div class="match-player">';
                    echo '<img src="' . $match['user2_photo'] . '" class="match-player-avatar" alt="' . $match['user2_name'] . '">';
                    echo '<div class="match-player-name' . ($match['user2_vip'] ? ' vip' : '') . '">' . $match['user2_name'] . '</div>';
                    echo '<div class="match-player-score">Score: ' . $match['user2_score'] . '</div>';
                    if ($match['user2_vip']) echo '<div class="vip-badge">👑 VIP</div>';
                    echo '</div>';
                    
                    echo '</div>';
                    echo '<div class="match-status">' . ($match['completed'] ? 'Finished' : 'Active') . '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        
        <?php else: ?>
        <div class="no-battle">
            <h2>❌ No Active Battle</h2>
            <p>No active battle found for your league.</p>
            <p><a href="auto_pvp_manager.php">→ Start PVP Battle</a></p>
        </div>
        <?php endif; ?>
        
        <!-- Links -->
        <div class="pvp-links">
            <h3>🔗 Quick Links:</h3>
            <p><a href="auto_pvp_manager.php">→ Auto PVP Manager</a></p>
            <p><a href="advance_pvp_round.php">→ Advance Round</a></p>
            <p><a href="test_pvp_avatars.php">→ Test Avatars</a></p>
        </div>
    </div>
</div>

<style>
.pvp-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.pvp-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.pvp-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.league-name {
    background: #ffd700;
    color: #333;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
}

.battle-info {
    background: #e8f5e8;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    border-left: 4px solid #f6cf49;
}

.matches-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.match-card {
    border: 2px solid #ddd;
    border-radius: 10px;
    padding: 20px;
    background: #f9f9f9;
    text-align: center;
}

.match-header {
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 15px;
    color: #333;
}

.match-players {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 15px 0;
}

.match-player {
    text-align: center;
    flex: 1;
}

.match-player-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid #ddd;
    margin-bottom: 10px;
}

.match-player-name {
    font-weight: bold;
    margin: 5px 0;
    color: #333;
}

.match-player-score {
    color: #666;
    font-size: 14px;
}

.match-vs {
    font-size: 24px;
    font-weight: bold;
    color: #ff4444;
    margin: 0 20px;
}

.match-status {
    margin-top: 15px;
    padding: 8px;
    background: #e0e0e0;
    border-radius: 5px;
    font-weight: bold;
}

.vip-badge {
    color: #ffd700;
    font-size: 12px;
    margin-top: 5px;
}

.no-battle {
    text-align: center;
    padding: 40px;
    background: #fff3cd;
    border-radius: 10px;
    border-left: 4px solid #ffc107;
}

.pvp-links {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #eee;
}

.pvp-links a {
    display: inline-block;
    margin: 5px 0;
    padding: 8px 15px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.pvp-links a:hover {
    background: #0056b3;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'PvP Battles - Simple';
$pageCss = 'assets_css/pvp.css';
include 'template.php';
?>
