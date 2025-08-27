<?php
if (!isset($activePage)) { $activePage = ''; }
if (!isset($content)) { $content = ''; }
if (!isset($pageTitle)) { $pageTitle = ''; }
if (!isset($pageCss)) { $pageCss = ''; }
if (!isset($extraJs)) { $extraJs = ''; }
if (!isset($extraCss)) { $extraCss = []; }
if (!isset($baseHref)) {
    $path = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    $depth = $path === '' ? 0 : substr_count($path, '/');
    $baseHref = $depth ? str_repeat('../', $depth) : './';
}
if (!isset($hideNav)) { $hideNav = false; }
    $profilePhoto = 'default-avatar.png';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/includes/db.php';
        $stmt = $db->prepare('SELECT gallery FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
            if (!empty($gallery)) {
                $candidate = 'uploads/' . $_SESSION['user_id'] . '/' . $gallery[0];
                if (is_file($candidate)) {
                    $profilePhoto = $candidate;
                }
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <base href="<?= htmlspecialchars($baseHref) ?>">
    <link rel="stylesheet" href="assets_css/template.css">
    <link rel="stylesheet" href="assets_css/message-notification.css">
    <link rel="stylesheet" href="moneysistem/money.css">
    <?php if ($pageCss): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
    <?php foreach ((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="app-frame">
    <div class="top-bar">
        <a href="diverse.php" class="nav-btn top-btn"><i class="fas fa-ellipsis-h"></i></a>
        <a href="mesaje.php" class="nav-btn top-btn center-btn"><i class="fas fa-envelope"></i><span id="messageIndicator" class="message-indicator"></span></a>
        <div class="top-right">
            <?php include __DIR__ . '/moneysistem/money.php'; ?>
            <div class="profile-box"><img src="<?= htmlspecialchars($profilePhoto) ?>" alt="Profile"></div>
        </div>
    </div>
    <a href="logout.php" class="nav-btn top-btn logout-btn"><i class="fas fa-right-from-bracket"></i></a>
    <div class="content">
        <?= $content ?>
    </div>
    <?php if (!$hideNav): ?>
    <nav class="bottom-nav">
        <a href="welcome.php" class="nav-btn <?php if($activePage==='welcome') echo 'active';?>"><i class="fas fa-seedling"></i></a>
        <a href="barn.php" class="nav-btn <?php if($activePage==='barn') echo 'active';?>"><i class="fas fa-warehouse"></i></a>
        <a href="profile.php" class="nav-btn <?php if($activePage==='profile') echo 'active';?>"><i class="fas fa-user"></i></a>
        <a href="friends.php" class="nav-btn <?php if($activePage==='friends') echo 'active';?>"><i class="fas fa-user-friends"></i></a>
        <a href="shop.php" class="nav-btn <?php if($activePage==='shop') echo 'active';?>"><i class="fas fa-store"></i></a>
        <a href="diverse.php" class="nav-btn <?php if($activePage==='diverse') echo 'active';?>"><i class="fas fa-table-cells-large"></i></a>
        <a href="settings.php" class="nav-btn <?php if($activePage==='settings') echo 'active';?>"><i class="fas fa-cog"></i></a>
    </nav>
    <?php endif; ?>
</div>
<?php if (!$noScroll): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const content = document.querySelector('.content');
    let isDown = false;
    let startY;
    let scrollTop;

    content.addEventListener('mousedown', function(e) {
        isDown = true;
        startY = e.pageY - content.offsetTop;
        scrollTop = content.scrollTop;
    });

    content.addEventListener('mouseleave', function() {
        isDown = false;
    });

    content.addEventListener('mouseup', function() {
        isDown = false;
    });

    content.addEventListener('mousemove', function(e) {
        if (!isDown) return;
        e.preventDefault();
        const y = e.pageY - content.offsetTop;
        const walk = y - startY;
        content.scrollTop = scrollTop - walk;
    });

    content.addEventListener('touchstart', function(e) {
        startY = e.touches[0].pageY - content.offsetTop;
        scrollTop = content.scrollTop;
    }, { passive: true });

    content.addEventListener('touchmove', function(e) {
        const y = e.touches[0].pageY - content.offsetTop;
        const walk = y - startY;
        content.scrollTop = scrollTop - walk;
    }, { passive: true });
});
</script>
<?php endif; ?>
<script src="assets_js/base-url.js"></script>
<script src="assets_js/message-notification.js"></script>
<script src="moneysistem/money.js"></script>
<script src="assets_js/interaction-blocker.js"></script>
<?= $extraJs ?>
</body>
</html>