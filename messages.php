<?php
session_start();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesaje</title>
    <link rel="stylesheet" href="assets_css/messages.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- HEADER CENTRALIZAT -->
    <div class="main-header">
        <span class="header-title"><i class="fas fa-comments"></i> Mesaje</span>
    </div>

    <!-- CONTAINER CENTRAL MARE -->
    <div class="messages-main-container">
      <div class="messages-wrapper">
        <!-- Lista persoane (stânga) -->
        <div class="messages-list" id="messagesList">
          <!-- Aici apar persoane cu care ai vorbit -->
          <div class="message-user active" onclick="selectConversation(0)">
            <img class="message-user-avatar" src="default-avatar.jpg" alt="Avatar">
            <div class="message-user-info">
              <div class="message-user-name">Kate, <span class="user-age">32</span> <span class="msg-dot"></span></div>
              <div class="message-user-preview">Hey! Ce faci?</div>
            </div>
            <div class="message-user-meta">
              <span class="msg-unread">2</span>
            </div>
          </div>
          <div class="message-user" onclick="selectConversation(1)">
            <img class="message-user-avatar" src="default-avatar.jpg" alt="Avatar">
            <div class="message-user-info">
              <div class="message-user-name">Eve, <span class="user-age">27</span></div>
              <div class="message-user-preview">Vii la party?</div>
            </div>
          </div>
          <div class="message-user" onclick="selectConversation(2)">
            <img class="message-user-avatar" src="default-avatar.jpg" alt="Avatar">
            <div class="message-user-info">
              <div class="message-user-name">Suzane, <span class="user-age">33</span> <span class="msg-dot"></span></div>
              <div class="message-user-preview">Salut, salut!</div>
            </div>
            <div class="message-user-meta">
              <span class="msg-unread">1</span>
            </div>
          </div>
          <div class="message-user" onclick="selectConversation(3)">
            <img class="message-user-avatar" src="default-avatar.jpg" alt="Avatar">
            <div class="message-user-info">
              <div class="message-user-name">John, <span class="user-age">28</span></div>
              <div class="message-user-preview">Ok, mersi!</div>
            </div>
          </div>
        </div>

        <!-- Conversație (dreapta) -->
        <div class="messages-conversation" id="messagesConversation">
          <div class="messages-conv-header">
            <img class="conv-avatar" src="default-avatar.jpg" alt="Avatar">
            <div>
              <span class="conv-username">Kate, 32</span>
              <span class="conv-status">Online</span>
            </div>
            <div class="conv-menu">
              <i class="fas fa-ellipsis-v"></i>
            </div>
          </div>
          <div class="messages-conv-body" id="messagesConvBody">
            <!-- Mesaje de conversație (dummy) -->
            <div class="msg-row">
              <div class="msg-bubble">Hey! Ce faci? <span class="msg-time">14:18</span></div>
            </div>
            <div class="msg-row own">
              <div class="msg-bubble own">Bine, tu? <span class="msg-time">14:19</span></div>
            </div>
            <div class="msg-row">
              <div class="msg-bubble">Tot bine! 😊 <span class="msg-time">14:19</span></div>
            </div>
            <div class="msg-row own">
              <div class="msg-bubble own">Ce planuri ai diseară? <span class="msg-time">14:20</span></div>
            </div>
          </div>
          <div class="messages-conv-footer">
            <input type="text" placeholder="Mesaj..." id="convInput" autocomplete="off" />
            <button class="conv-send-btn"><i class="fas fa-paper-plane"></i></button>
          </div>
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
    <script src="assets_js/messages.js"></script>
</body>
</html>
