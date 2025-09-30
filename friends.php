<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

function build_card($u) {
    $avatar = 'default-avatar.png';
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
        'status' => $status,
        'vip' => !empty($u['vip']),
        'last_active' => $last
    ];
}

$pendingSent = [];
$pendingReceived = [];
$friendIds = [];
try {
    $stmt = $db->prepare('SELECT sender_id, receiver_id, status FROM friend_requests WHERE sender_id = ? OR receiver_id = ?');
    $stmt->execute([$user_id, $user_id]);
    while ($fr = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $other = ($fr['sender_id'] == $user_id) ? $fr['receiver_id'] : $fr['sender_id'];
        if ($fr['status'] === 'pending') {
            if ($fr['sender_id'] == $user_id) {
                $pendingSent[] = $other;
            } else {
                $pendingReceived[] = $other;
            }
        } elseif ($fr['status'] === 'accepted') {
            $friendIds[] = $other;
        }
    }
} catch (PDOException $e) {
}
$excludeIds = array_unique($pendingReceived);

$rawUsers = [];
try {
    $stmt = $db->prepare('SELECT id, username, gallery, vip, last_active FROM users WHERE id != ?');
    $stmt->execute([$user_id]);
    $rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stmt = $db->prepare('SELECT id, username, gallery, vip FROM users WHERE id != ?');
    $stmt->execute([$user_id]);
    $rawUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rawUsers as &$u) {
        $u['last_active'] = date('Y-m-d H:i:s');
    }
}

$allUsers = [];
foreach ($rawUsers as $u) {
    if (in_array($u['id'], $excludeIds)) {
        continue;
       }
    $card = build_card($u);
    if (in_array($u['id'], $pendingSent)) {
        $card['requestSent'] = true;
    }
    if (in_array($u['id'], $friendIds)) {
        $card['isFriend'] = true;
    }
    $allUsers[] = $card;
}

$statusOrder = ['online' => 0, 'idle' => 1, 'offline' => 2];
usort($allUsers, function ($a, $b) use ($statusOrder) {
    $sa = $statusOrder[$a['status']] ?? 3;
    $sb = $statusOrder[$b['status']] ?? 3;
    if ($sa === $sb) {
        return strcasecmp($a['username'], $b['username']);
    }
    return $sa <=> $sb;
});

$friendRequests = [];
try {
    $stmt = $db->prepare('SELECT u.id, u.username, u.gallery, u.vip, u.last_active
        FROM friend_requests fr
        JOIN users u ON u.id = fr.sender_id
        WHERE fr.receiver_id = ? AND fr.status = \'pending\'');
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
    $stmt = $db->prepare('SELECT u.id, u.username, u.gallery, u.vip, u.last_active
        FROM friend_requests fr
        JOIN users u ON u.id = CASE WHEN fr.sender_id = ? THEN fr.receiver_id ELSE fr.sender_id END
        WHERE (fr.sender_id = ? OR fr.receiver_id = ?) AND fr.status = \'accepted\'');
    $stmt->execute([$user_id, $user_id, $user_id]);
    $friendRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($friendRows as $r) {
        $friends[] = build_card($r);
    }
} catch (PDOException $e) {
    $friends = [];
}

ob_start();
?>
<div class="matches-container">
    <div class="matches-tabs">
        <button class="tab-btn active" data-tab="online">Online Users</button>
        <button class="tab-btn" data-tab="requests">Friend Requests <span id="friendRequestsIndicator" class="friend-indicator"></span></button>
        <button class="tab-btn" data-tab="friends">Friends</button>
    </div>
    <div class="matches-search">
        <input type="text" id="searchInput" placeholder="Search by name">
        <button id="searchBtn">Search</button>
    </div>
    <div id="cardContainer" class="card-container"></div>
</div>
<?php
$content = ob_get_clean();
$activePage = 'friends';
$pageCss = 'assets_css/friends.css';
$extraJs = "<script>var allUsers = " . json_encode($allUsers, JSON_UNESCAPED_UNICODE) . "; var friendRequests = " . json_encode($friendRequests, JSON_UNESCAPED_UNICODE) . "; var friends = " . json_encode($friends, JSON_UNESCAPED_UNICODE) . ";</script>\n<script src=\"assets_js/friends.js\"></script>";
include 'template.php';
?>
