<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}
require_once __DIR__ . '/includes/db.php';
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (int)$_SESSION['user_id'];

// Ensure slot_helpers table exists
$db->exec('CREATE TABLE IF NOT EXISTS slot_helpers (
    owner_id INT NOT NULL,
    slot_number INT NOT NULL,
    helper_id INT NOT NULL,
    water_clicks INT NOT NULL DEFAULT 0,
    feed_clicks INT NOT NULL DEFAULT 0,
    last_action_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (owner_id, slot_number, helper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci');

$stmt = $db->prepare(
    'SELECT sh.helper_id, u.username, u.gallery,
            SUM(sh.water_clicks) AS water,
            SUM(sh.feed_clicks) AS feed,
            MAX(sh.last_action_at) AS last_action,
            (SUM(sh.water_clicks) + SUM(sh.feed_clicks)) AS total
     FROM slot_helpers sh
     JOIN users u ON u.id = sh.helper_id
     WHERE sh.owner_id = ?
     GROUP BY sh.helper_id
     ORDER BY last_action DESC, total DESC
     LIMIT 6'
);
$stmt->execute([$userId]);
$helpers = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $gallery = !empty($row['gallery']) ? array_filter(explode(',', $row['gallery'])) : [];
    $photo = 'default-avatar.png';
    if (!empty($gallery)) {
        $candidate = 'uploads/' . $row['helper_id'] . '/' . $gallery[0];
        if (is_file($candidate)) {
            $photo = $candidate;
        }
    }
    $helpers[] = [
        'id' => (int)$row['helper_id'],
        'username' => $row['username'],
        'photo' => $photo,
        'water' => (int)$row['water'],
        'feed' => (int)$row['feed'],
        'last' => $row['last_action']
    ];
}

echo json_encode($helpers);
