<?php
// includes_core
require_once 'includes_core/config.php';
require_once 'includes_core/db.php';
require_once 'includes_core/session.php';
require_once 'includes_core/auth.php';

// UI header și navbar
include 'includes_ui/header.php';
include 'includes_ui/navbar.php';
?>

<main class="main-content">

    <!-- Secțiune profil -->
    <?php include 'includes_ui/profile_card.php'; ?>

    <!-- Secțiune like-uri -->
    <?php include 'includes_ui/like_button.php'; ?>

    <!-- Secțiune mesaje -->
    <?php include 'includes_ui/message_box.php'; ?>

    <!-- Notificări -->
    <?php include 'includes_ui/notification_box.php'; ?>

</main>

<?php
// Footer UI
include 'includes_ui/footer.php';
?>

<!-- Scripturi principale -->
<link rel="stylesheet" href="assets_css/index.css">
<script src="assets_js/main.js"></script>
<script src="assets_js/notifications.js"></script>
<script src="assets_js/messages.js"></script>
<script src="assets_js/modals.js"></script>
