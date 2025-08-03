<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

function build_card($u) {
    $avatar = 'img/user_default.png';
    if (!empty($u['gallery'])) {
        $gal = explode(',', $u['gallery']);
        $avatar = 'uploads/' . $u['id'] . '/' . trim($gal[0]);
    }
    $last = isset($u['last_active']) ? strtotime($u['last_active']) : time();
    $diff = time() - $last;
    if ($diff <= 300) {
        $status = 'online';
    } elseif ($diff <= 1200) {
        $status = 'idle';
    } else {
        $status = 'offline';
    }
    return [
        'id' => $u['id'],
        'username' => $u['username'],
        'avatar' => $avatar,
        'status' => $status
    ];
}

$pendingIds = [];
$friendIds = [];
try {
    $stmt = $db->prepare('SELECT sender_id, receiver_id, status FROM friend_requests WHERE sender_id = ? OR receiver_id = ?');
    $stmt->execute([$user_id, $user_id]);
    while ($fr = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $other = ($fr['sender_id'] == $user_id) ? $fr['receiver_id'] : $fr['sender_id'];
        if ($fr['status'] === 'pending') {
            $pendingIds[] = $other;
        } elseif ($fr['status'] === 'accepted') {
            $friendIds[] = $other;
        }
    }
} catch (PDOException $e) {
}
$excludeIds = array_unique(array_merge($pendingIds, $friendIds));

$rawUsers = [];
try {
    $stmt = $db->prepare('SELECT id, username, gallery, last_active FROM users WHERE id != ?');
    $stmt->execute([$user_id]);
    $rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stmt = $db->prepare('SELECT id, username, gallery FROM users WHERE id != ?');
    $stmt->execute([$user_id]);
    $rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rawUsers as &$u) {
        $u['last_active'] = date('Y-m-d H:i:s');
    }
}

$onlineUsers = [];
$now = time();
foreach ($rawUsers as $u) {
    if (in_array($u['id'], $excludeIds)) {
        continue;
    }
    $last = strtotime($u['last_active']);
    if ($now - $last > 1200) {
        continue;
    }
    $onlineUsers[] = build_card($u);
}

$friendRequests = [];
try {
    $stmt = $db->prepare('SELECT u.id, u.username, u.gallery, u.last_active
        FROM friend_requests fr
        JOIN users u ON u.id = fr.sender_id
        WHERE fr.receiver_id = ? AND fr.status = "pending"');
    $stmt->execute([$user_id]);
    $reqRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($reqRows as $r) {
        $friendRequests[] = build_card($r);
    }
} catch (PDOException $e) {
    $friendRequests = [];
}

$friends = [];
try {
    $stmt = $db->prepare('SELECT u.id, u.username, u.gallery, u.last_active
        FROM friend_requests fr
        JOIN users u ON u.id = CASE WHEN fr.sender_id = ? THEN fr.receiver_id ELSE fr.sender_id END
        WHERE (fr.sender_id = ? OR fr.receiver_id = ?) AND fr.status = "accepted"');
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friendRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($friendRows as $r) {
        $friends[] = build_card($r);
    }
} catch (PDOException $e) {
    $friends = [];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potriviri</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="assets_css/matches.css">
    <link rel="stylesheet" href="assets_css/mini-profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="main-header">
        <a href="logout.php" class="logout-btn" title="Deconectare">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <span class="header-title">POTRIVIRI</span>
    </div>
    <div class="profile-container">
        <div class="matches-container">
        <div class="matches-tabs">
            <button class="tab-btn active" data-tab="online">Utilizatori online</button>
            <button class="tab-btn" data-tab="requests">Cererile de prietenie</button>
            <button class="tab-btn" data-tab="friends">Prieteni</button>
        </div>
        <div class="matches-search">
            <input type="text" id="searchInput" placeholder="Caută după nume">
            <button id="searchBtn">Caută</button>
        </div>
        <div id="cardContainer" class="card-container"></div>
        </div>
    </div>
    <div class="navbar">
        <a class="icon" href="index.php"><i class="fas fa-home"></i></a>
        <a class="icon active" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="icon" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="icon" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
    <script>
        let onlineUsers = <?=json_encode($onlineUsers, JSON_UNESCAPED_UNICODE)?>;
        let friendRequests = <?=json_encode($friendRequests, JSON_UNESCAPED_UNICODE)?>;
        let friends = <?=json_encode($friends, JSON_UNESCAPED_UNICODE)?>;
    </script>
    <script src="assets_js/matches.js"></script>
</body>
</html>