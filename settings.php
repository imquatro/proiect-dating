<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Redirect to profile settings page
header('Location: settings_profile.php');
exit;
?>