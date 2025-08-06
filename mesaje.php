<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';
$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0);
$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$friend = null;
$friend_avatar = 'default-avatar.png';
if ($friend_id > 0) {
    $stmt = $db->prepare('SELECT id, username, gallery FROM users WHERE id = ?');
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($friend && !empty($friend['gallery'])) {
        $gal = array_filter(explode(',', $friend['gallery']));
        if (!empty($gal)) {
            $candidate = 'uploads/' . $friend['id'] . '/' . trim($gal[0]);
            if (is_file($candidate)) {
                $friend_avatar = $candidate;
            }
        }
    }
}
ob_start();
if (!$friend) {
    $db->exec('CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL
    )');
    $stmt = $db->prepare('SELECT u.id, u.username, u.gallery
        FROM users u
        JOIN messages m ON (m.sender_id = u.id OR m.receiver_id = u.id)
        WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
        GROUP BY u.id, u.username, u.gallery
        ORDER BY MAX(m.created_at) DESC');
    $stmt->execute([$user_id, $user_id, $user_id]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($conversations) {
        echo '<ul class="conversation-list">';
        foreach ($conversations as $c) {
            $avatar = 'default-avatar.png';
            if (!empty($c['gallery'])) {
                $gal = array_filter(explode(',', $c['gallery']));
                if (!empty($gal)) {
                    $candidate = 'uploads/' . $c['id'] . '/' . trim($gal[0]);
                    if (is_file($candidate)) {
                        $avatar = $candidate;
                    }
                }
            }
            echo '<li><a href="mesaje.php?id=' . (int)$c['id'] . '"><img src="' . htmlspecialchars($avatar) . '" class="conv-avatar" alt="">' . htmlspecialchars($c['username']) . '</a></li>';
        }
        echo '</ul>';
    } else {
        echo '<p>Nu ai conversații.</p>';
    }
} else {
?>
<div class="chat-container">
    <div class="chat-header"><?= htmlspecialchars($friend['username']) ?></div>
    <div id="chatMessages" class="chat-messages"></div>
    <div id="typingIndicator" class="typing-indicator"><img src="<?= htmlspecialchars($friend_avatar) ?>" class="typing-avatar" alt=""><span></span><span></span><span></span></div>
    <form id="messageForm" class="chat-input" autocomplete="off">
        <input type="text" id="messageInput" placeholder="Scrie un mesaj...">
        <button type="submit">Trimite</button>
    </form>
</div>
<?php
}
$content = ob_get_clean();
$pageTitle = 'Mesaje';
$pageCss = 'assets_css/mesaje.css';
$extraJs = $friend ? "<script>var friendId = $friend_id; var currentUserId = $user_id;</script>\n<script src=\"assets_js/mesaje.js\"></script>" : '';
include 'template.php';