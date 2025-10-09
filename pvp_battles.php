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

// Verificăm liga userului
$stmt = $db->prepare("SELECT league_id FROM user_league_status WHERE user_id = ?");
$stmt->execute([$userId]);
$userLeagueId = $stmt->fetchColumn();

if (!$userLeagueId) {
    // Dacă nu e în nicio ligă, îl punem în bronz
    $db->prepare("INSERT INTO user_league_status (user_id, league_id) VALUES (?, 1)")->execute([$userId]);
    $userLeagueId = 1;
}

// Luăm toate ligile
$stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
$leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Luăm battle-ul activ pentru liga userului
$stmt = $db->prepare("SELECT * FROM pvp_battles WHERE league_id = ? AND is_active = 1 ORDER BY id DESC LIMIT 1");
$stmt->execute([$userLeagueId]);
$activeBattle = $stmt->fetch(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="pvp-container">
    <div id="pvpPanel" class="pvp-panel">
        <!-- Header cu Timer și Status -->
        <div class="pvp-header">
            <div class="pvp-timer-container">
                <div id="pvpTimer" class="pvp-timer">
                    <i class="fas fa-clock"></i> 
                    <span id="timerText">Se încarcă...</span>
                </div>
                <div id="pvpStatus" class="pvp-status">Se încarcă...</div>
            </div>
            <div class="pvp-user-league">
                <span class="league-label">Liga ta:</span>
                <span id="userLeagueName" class="league-name" data-league-id="<?= $userLeagueId ?>">
                    <?php 
                    $userLeague = array_filter($leagues, fn($l) => $l['id'] == $userLeagueId);
                    $userLeague = reset($userLeague);
                    echo $userLeague ? $userLeague['name'] : 'Bronz';
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
        
        <!-- Conținut Tabs -->
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
                    <button class="round-btn" data-round="4">1/4 (Semifinală)</button>
                    <button class="round-btn" data-round="5">Finală</button>
                </div>
                
                <!-- Loading -->
                <div class="bracket-loading" id="loading-<?= $league['id'] ?>">
                    <i class="fas fa-spinner fa-spin"></i> Se încarcă bracket-ul...
                </div>
                
                <!-- Bracket Container -->
                <div class="bracket-container" id="bracket-<?= $league['id'] ?>" style="display: none;">
                    <!-- Generat dinamic prin JS -->
                </div>
                
                <!-- No Battle Message -->
                <div class="no-battle-message" id="noBattle-<?= $league['id'] ?>" style="display: none;">
                    <i class="fas fa-info-circle"></i>
                    <p>Nu există battle activ pentru această ligă momentan.</p>
                    <p class="next-battle-info">Următorul battle va începe în curând!</p>
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

<?php
$content = ob_get_clean();
$pageCss = 'assets_css/pvp.css';
$extraJs = '<script src="assets_js/pvp.js?v=' . time() . '"></script>';
include 'template.php';
?>

