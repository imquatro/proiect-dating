<?php
if (!isset($activePage)) { $activePage = ''; }
if (!isset($content)) { $content = ''; }
if (!isset($pageTitle)) { $pageTitle = ''; }
if (!isset($pageCss)) { $pageCss = ''; }
if (!isset($extraJs)) { $extraJs = ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="assets_css/template.css">
    <?php if ($pageCss): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="app-frame">
    <div class="top-bar">
        <a href="diverse.php" class="nav-btn top-btn"><i class="fas fa-ellipsis-h"></i></a>
        <a href="mesaje.php" class="nav-btn top-btn center-btn"><i class="fas fa-envelope"></i></a>
        <div class="profile-box"></div>
    </div>
    <div class="content">
        <?= $content ?>
    </div>
    <nav class="bottom-nav">
        <a href="welcome.php" class="nav-btn <?php if($activePage==='welcome') echo 'active';?>"><i class="fas fa-seedling"></i></a>
        <a href="barn.php" class="nav-btn <?php if($activePage==='barn') echo 'active';?>"><i class="fas fa-warehouse"></i></a>
        <a href="profile.php" class="nav-btn <?php if($activePage==='profile') echo 'active';?>"><i class="fas fa-user"></i></a>
        <a href="friends.php" class="nav-btn <?php if($activePage==='friends') echo 'active';?>"><i class="fas fa-user-friends"></i></a>
        <a href="shop.php" class="nav-btn <?php if($activePage==='shop') echo 'active';?>"><i class="fas fa-store"></i></a>
        <a href="settings.php" class="nav-btn <?php if($activePage==='settings') echo 'active';?>"><i class="fas fa-cog"></i></a>
    </nav>
</div>
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
<?= $extraJs ?>
</body>
</html>