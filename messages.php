<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Verificăm dacă userul este admin
$stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$isAdmin = $stmt->fetchColumn();

// Preluare lista useri cu care s-a conversat
// Ordonăm după ultimul mesaj
$stmt = $db->prepare("
    SELECT u.id, u.username, u.age, u.city, u.country, u.gender, u.gallery,
           MAX(m.created_at) as last_msg, m.message
    FROM users u
    JOIN (
        SELECT 
            IF(sender_id = :uid, receiver_id, sender_id) as other_id,
            message,
            created_at
        FROM messages
        WHERE sender_id = :uid OR receiver_id = :uid
    ) m ON u.id = m.other_id
    WHERE u.id != :uid
    GROUP BY u.id
    ORDER BY last_msg DESC
");
$stmt->execute(['uid' => $user_id]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Selectează cu cine vorbim doar dacă este specificat în GET
$selected_user_id = null;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $selected_user_id = (int)$_GET['user_id'];
}

// Preluare user selectat (doar dacă avem conversație)
$selected_user = null;
if ($selected_user_id) {
    $stmt = $db->prepare("SELECT id, username, age, city, country, gender, gallery FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Inserare mesaj nou
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'], $_POST['message']) && $selected_user_id) {
    $msg = trim($_POST['message']);
    if ($msg !== '' && $selected_user_id != $user_id) {
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $selected_user_id, $msg]);
        header("Location: messages.php?user_id=" . $selected_user_id);
        exit;
    }
}

// Preluare mesaje între user curent și cel selectat
$messages = [];
if ($selected_user_id) {
    $stmt = $db->prepare("
        SELECT m.*, u.username, u.gallery
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = :uid AND receiver_id = :sid) OR (sender_id = :sid AND receiver_id = :uid)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute(['uid' => $user_id, 'sid' => $selected_user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Mesaje</title>
    <link rel="stylesheet" href="assets_css/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- HEADER CENTRALIZAT -->
    <div class="main-header">
        <span class="header-title">MESAGERIE</span>
    </div>
    <!-- CONTAINER MESAGERIE -->
    <div class="messages-main-container">
        <div class="messages-wrapper">
            <!-- LISTA CONTACTE -->
            <div class="messages-list">
                <?php if ($contacts): ?>
                    <?php foreach ($contacts as $contact): 
                        // Imagine: prima din galerie sau default-avatar
                        $avatar = 'default-avatar.jpg';
                        if (!empty($contact['gallery'])) {
                            $gal = explode(',', $contact['gallery']);
                            $avatar = trim($gal[0]);
                        }
                        ?>
                        <div class="message-user<?= $contact['id'] == $selected_user_id ? ' active' : '' ?>"
                             data-user-id="<?=$contact['id']?>"
                             data-username="<?=htmlspecialchars($contact['username'])?>">
                            <img src="<?=htmlspecialchars($avatar)?>" alt="" class="message-user-avatar">
                            <div class="message-user-info">
                                <div class="message-user-name"><?=htmlspecialchars($contact['username'])?></div>
                                <div class="message-user-preview"><?=htmlspecialchars($contact['message'])?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding:28px;color:#bb87ff;">Nicio conversație încă. Trimite un mesaj!</div>
                <?php endif; ?>
            </div>
            <!-- ZONA CONVERSAȚIE -->
            <div class="messages-conversation">
                <?php if ($selected_user): ?>
                    <div class="messages-conv-header">
                        <?php 
                        $avatar2 = 'default-avatar.jpg';
                        if (!empty($selected_user['gallery'])) {
                            $gal2 = explode(',', $selected_user['gallery']);
                            $avatar2 = trim($gal2[0]);
                        }
                        ?>
                        <img src="<?=htmlspecialchars($avatar2)?>" alt="" class="conv-avatar">
                        <span class="conv-username"><?=htmlspecialchars($selected_user['username'])?></span>
                        <span class="conv-status">online</span>
                    </div>
                    <div class="messages-conv-body" id="messagesConvBody">
                        <?php foreach ($messages as $msg):
                            $msg_avatar = 'default-avatar.jpg';
                            if (!empty($msg['gallery'])) {
                                $galm = explode(',', $msg['gallery']);
                                $msg_avatar = trim($galm[0]);
                            }
                        ?>
                            <div class="msg-row<?= $msg['sender_id'] == $user_id ? ' own' : '' ?>">
                                <?php if ($msg['sender_id'] != $user_id): ?>
                                    <img src="<?=htmlspecialchars($msg_avatar)?>" alt="" class="msg-avatar">
                                <?php endif; ?>
                                <div class="msg-bubble<?= $msg['sender_id'] == $user_id ? ' own' : '' ?>">
                                    <?=htmlspecialchars($msg['message'])?>
                                </div>
                                <span class="msg-time"><?=date('H:i', strtotime($msg['created_at']))?></span>
                                <?php if ($msg['sender_id'] == $user_id): ?>
                                    <img src="<?=htmlspecialchars($msg_avatar)?>" alt="" class="msg-avatar">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- FORM MESAJ -->
                    <form class="messages-conv-footer" method="post" autocomplete="off" style="margin-top:2px;">
                        <input type="text" name="message" placeholder="Scrie un mesaj..." required maxlength="512" autocomplete="off" style="font-size:1.16em;">
                        <button type="submit" name="send_message" class="conv-send-btn"><i class="fas fa-paper-plane"></i></button>
                    </form>
                <?php else: ?>
                    <div class="messages-conv-header" style="min-height:200px;justify-content:center;align-items:center;">
                        <span style="color:#bbb;font-size:1.13em;">Selectează sau trimite un mesaj cuiva!</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- MODAL ȘTERGERE CONVERSAȚIE -->
    <div id="deleteModal" class="modal-overlay" style="display:none;">
        <div class="modal">
            <p id="deleteModalText">Ștergi conversația?</p>
            <div class="modal-actions">
                <button id="deleteConfirm">Șterge</button>
                <button id="deleteCancel">Anulează</button>
            </div>
        </div>
    </div>
    <!-- NAVBAR CENTRALIZAT -->
    <div class="navbar">
        <a class="icon" href="index.php"><i class="fas fa-home"></i></a>
        <a class="icon" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="icon active" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="icon" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
    <script>
    // Scroll la ultimul mesaj
    window.onload = function() {
        var msgBody = document.getElementById('messagesConvBody');
        if(msgBody) msgBody.scrollTop = msgBody.scrollHeight;
    };
    </script>
</body>
</html>
