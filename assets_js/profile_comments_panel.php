<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit;
}
?>
<div id="profile-comments-panel"></div>