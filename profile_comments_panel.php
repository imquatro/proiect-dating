<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit;
}
ob_start();
$mini_profile_config = [
    'show_helpers' => false,
    'show_profile' => true,
    'show_achievements' => false,
    'show_helper_effect' => false,
    'center_single' => false,
];
include 'mini_profile.php';
$miniHtml = ob_get_clean();
$miniHtml = str_replace('id="miniProfile"', 'id="panelMiniProfile"', $miniHtml);
$miniHtml = preg_replace('#<script[^>]*mini-profile.js[^>]*></script>#', '', $miniHtml);
?>
<div id="profile-comments-panel">
    <?= $miniHtml ?>
    <div class="helpers-bar" id="helper-avatars"></div>
    <div class="comments-section">
        <div class="comments-list" id="comments-list"></div>
        <form id="comment-form" autocomplete="off">
            <input type="text" id="comment-input" placeholder="Scrie un comentariu...">
            <button type="submit">Trimite</button>
        </form>
    </div>
</div>
