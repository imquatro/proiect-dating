<?php
/**
 * PvP System Test Script
 * Rulează pentru a testa și verifica funcționalitatea sistemului
 */

session_start();

// Simulăm un user logat pentru test
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // User ID pentru test
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/pvp_helpers.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PvP System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4caf50;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 10px;
        }
        .success {
            color: #4caf50;
            font-weight: bold;
        }
        .error {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            color: #2196f3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #4caf50;
            color: white;
        }
        .btn {
            background: #4caf50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #da190b;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-left: 3px solid #4caf50;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🏆 PvP System - Test & Debug Panel</h1>

    <!-- Test 1: Verificare Tabele -->
    <div class="test-section">
        <h2>1. Verificare Tabele Database</h2>
        <?php
        $tables = ['pvp_leagues', 'pvp_battles', 'pvp_participants', 'pvp_matches', 'user_league_status'];
        echo "<table>";
        echo "<tr><th>Tabelă</th><th>Status</th><th>Rânduri</th></tr>";
        
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) FROM $table");
                $count = $stmt->fetchColumn();
                echo "<tr><td>$table</td><td class='success'>✓ Există</td><td>$count</td></tr>";
            } catch (PDOException $e) {
                echo "<tr><td>$table</td><td class='error'>✗ Nu există</td><td>-</td></tr>";
            }
        }
        echo "</table>";
        ?>
    </div>

    <!-- Test 2: Verificare Ligi -->
    <div class="test-section">
        <h2>2. Ligi Disponibile</h2>
        <?php
        $stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level ASC");
        $leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($leagues)) {
            echo "<p class='error'>✗ Nu există ligi! Rulează pvp_helpers.php pentru a crea ligile.</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Nume</th><th>Level</th><th>Culoare</th><th>Min Jucători</th></tr>";
            foreach ($leagues as $league) {
                echo "<tr>";
                echo "<td>{$league['id']}</td>";
                echo "<td>{$league['name']}</td>";
                echo "<td>{$league['level']}</td>";
                echo "<td style='color:{$league['color']}'>{$league['color']}</td>";
                echo "<td>{$league['min_players']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <!-- Test 3: Status User -->
    <div class="test-section">
        <h2>3. Status User Curent (ID: <?= $_SESSION['user_id'] ?>)</h2>
        <?php
        $userId = $_SESSION['user_id'];
        
        // Verificăm liga userului
        $stmt = $db->prepare("SELECT uls.*, l.name as league_name FROM user_league_status uls JOIN pvp_leagues l ON uls.league_id = l.id WHERE uls.user_id = ?");
        $stmt->execute([$userId]);
        $userLeague = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userLeague) {
            echo "<p class='success'>✓ User este în liga: <strong>{$userLeague['league_name']}</strong></p>";
            echo "<p>Total câștiguri: {$userLeague['total_wins']} | Total pierderi: {$userLeague['total_losses']}</p>";
        } else {
            echo "<p class='info'>ℹ User nu este în nicio ligă. Se va aloca automat la prima vizită.</p>";
        }
        
        // Calculăm scorul de popularitate
        $score = calculatePopularityScore($userId);
        echo "<p>Scor popularitate curent: <strong>$score</strong> puncte</p>";
        ?>
    </div>

    <!-- Test 4: Battles Active -->
    <div class="test-section">
        <h2>4. Battles Active</h2>
        <?php
        $stmt = $db->query("SELECT b.*, l.name as league_name FROM pvp_battles b JOIN pvp_leagues l ON b.league_id = l.id WHERE b.is_active = 1 ORDER BY b.id DESC");
        $battles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($battles)) {
            echo "<p class='info'>ℹ Nu există battles active momentan.</p>";
            echo "<p><a href='?action=create_test_battle' class='btn'>Creează Battle de Test</a></p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Ligă</th><th>Start</th><th>Rundă</th><th>Status</th><th>Acțiuni</th></tr>";
            foreach ($battles as $battle) {
                echo "<tr>";
                echo "<td>#{$battle['id']}</td>";
                echo "<td>{$battle['league_name']}</td>";
                echo "<td>{$battle['start_date']}</td>";
                echo "<td>{$battle['current_round']}</td>";
                echo "<td>{$battle['status']}</td>";
                echo "<td><a href='pvp_battles.php' class='btn'>Vezi</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </div>

    <!-- Test 5: Actions -->
    <div class="test-section">
        <h2>5. Acțiuni Rapide</h2>
        <?php
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            
            if ($action === 'create_test_battle') {
                // Creăm un battle de test în liga Bronz
                $leagueId = 1;
                
                try {
                    $db->prepare("INSERT INTO pvp_battles (league_id, start_date, current_round, status, is_active) VALUES (?, NOW(), 1, 'active', 1)")->execute([$leagueId]);
                    $battleId = $db->lastInsertId();
                    
                    // Alocăm jucători (dacă sunt disponibili)
                    $playersCount = allocatePlayers($battleId, $leagueId);
                    
                    if ($playersCount > 0) {
                        createFirstRoundMatches($battleId);
                        echo "<p class='success'>✓ Battle #{$battleId} creat cu succes! Alocați $playersCount jucători.</p>";
                    } else {
                        echo "<p class='error'>✗ Nu s-au putut aloca jucători pentru battle.</p>";
                    }
                    
                    echo "<script>setTimeout(() => window.location.href = 'pvp_test.php', 2000);</script>";
                } catch (PDOException $e) {
                    echo "<p class='error'>✗ Eroare: {$e->getMessage()}</p>";
                }
            }
            
            if ($action === 'run_cron') {
                echo "<p class='info'>Rulăm cron job...</p>";
                require_once 'pvp_cron.php';
                runCron();
                echo "<p class='success'>✓ Cron executat!</p>";
                echo "<script>setTimeout(() => window.location.href = 'pvp_test.php', 2000);</script>";
            }
            
            if ($action === 'reset_leagues') {
                monthlyLeagueReset();
                echo "<p class='success'>✓ Reset ligi executat!</p>";
                echo "<script>setTimeout(() => window.location.href = 'pvp_test.php', 2000);</script>";
            }
        }
        ?>
        
        <a href="?action=create_test_battle" class="btn">Creează Battle Test</a>
        <a href="?action=run_cron" class="btn">Rulează Cron Manual</a>
        <a href="?action=reset_leagues" class="btn btn-danger">Reset Ligi (ATENȚIE!)</a>
        <a href="pvp_battles.php" class="btn">Mergi la PvP Battles</a>
    </div>

    <!-- Test 6: API Endpoints -->
    <div class="test-section">
        <h2>6. Test API Endpoints</h2>
        <div id="apiTest">
            <button onclick="testAPI('get_battle_status')" class="btn">Test Battle Status</button>
            <button onclick="testAPI('get_all_leagues')" class="btn">Test Get Leagues</button>
            <button onclick="testAPI('get_user_current_match')" class="btn">Test Current Match</button>
        </div>
        <pre id="apiResult">Click pe un buton pentru a testa API...</pre>
    </div>

    <script>
    function testAPI(action) {
        const resultEl = document.getElementById('apiResult');
        resultEl.textContent = 'Loading...';
        
        fetch(`pvp_api.php?action=${action}`)
            .then(r => r.json())
            .then(data => {
                resultEl.textContent = JSON.stringify(data, null, 2);
            })
            .catch(err => {
                resultEl.textContent = 'Error: ' + err.message;
            });
    }
    </script>

    <!-- Footer -->
    <div class="test-section">
        <p style="text-align: center; color: #666;">
            <strong>PvP System Test Panel</strong> - Verifică și testează toate funcționalitățile<br>
            <a href="PVP_SETUP_README.md" target="_blank">📖 Vezi Documentația Completă</a>
        </p>
    </div>
</body>
</html>

