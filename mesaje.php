<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

$friend_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$friend = null;
if ($friend_id > 0) {
    $stmt = $db->prepare('SELECT id, username FROM users WHERE id = ?');
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch(PDO::FETCH_ASSOC);
}

ob_start();
if (!$friend) {
    echo '<p>Selectează un prieten pentru a începe conversația.</p>';
} else {
?>
<div class="chat-container">
    <div class="chat-header"><?= htmlspecialchars($friend['username']) ?></div>
    <div id="chatMessages" class="chat-messages"></div>
    <div id="typingIndicator" class="typing-indicator"><span></span><span></span><span></span></div>
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
$extraJs = "<script>var friendId = $friend_id; var currentUserId = $user_id;</script>\n<script src=\"assets_js/mesaje.js\"></script>";
include 'template.php';