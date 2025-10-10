<?php
/**
 * PVP Database Setup - Ruleaza o data pentru a actualiza DB-ul
 * 
 * Acceseaza: http://localhost/1/setup_pvp_db.php
 */

echo "<h1>PVP Database Setup</h1>";
echo "<p>Actualizez baza de date pentru PVP...</p>";

// Include pvp_helpers.php care va face toate actualizarile
require_once 'includes/pvp_helpers.php';

echo "<h2>✅ Setup Complet!</h2>";
echo "<p>Toate coloanele si ligile au fost actualizate.</p>";

// Verifica ligile
echo "<h3>Ligile actuale:</h3>";
$stmt = $db->query("SELECT * FROM pvp_leagues ORDER BY level");
$leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Level</th><th>Color</th><th>Min Players</th></tr>";
foreach ($leagues as $league) {
    echo "<tr>";
    echo "<td>{$league['id']}</td>";
    echo "<td>{$league['name']}</td>";
    echo "<td>{$league['level']}</td>";
    echo "<td>{$league['color']}</td>";
    echo "<td>{$league['min_players']}</td>";
    echo "</tr>";
}
echo "</table>";

// Verifica coloanele users
echo "<h3>Coloanele din tabela users:</h3>";
$stmt = $db->query("SHOW COLUMNS FROM users");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<p>" . implode(", ", $columns) . "</p>";

// Verifica useri in league status
echo "<h3>Useri in league status:</h3>";
$stmt = $db->query("SELECT COUNT(*) as total FROM user_league_status");
$count = $stmt->fetchColumn();
echo "<p>Total useri in league status: <strong>$count</strong></p>";

echo "<hr>";
echo "<p><a href='farm_admin/panel.php'>← Back to Admin Panel</a></p>";
echo "<p><a href='pvp_battles.php'>→ Go to PVP Battles</a></p>";
?>
