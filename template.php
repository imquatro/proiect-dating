<?php
if (!isset($activePage)) { $activePage = ''; }
if (!isset($content)) { $content = ''; }
if (!isset($pageTitle)) { $pageTitle = ''; }
if (!isset($pageCss)) { $pageCss = ''; }
if (!isset($extraJs)) { $extraJs = ''; }
?>
<!DOCTYPE html>
<html lang="ro">
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
        <div class="page-title"><?= htmlspecialchars($pageTitle) ?></div>
        <div class="top-right">
            <a href="mesaje.php" class="nav-btn top-btn"><i class="fas fa-envelope"></i></a>
            <div class="profile-box"></div>
        </div>
    </div>
    <div class="content">
        <?= $content ?>
    </div>
    <nav class="bottom-nav">
        <a href="welcome.php" class="nav-btn <?php if($activePage==='welcome') echo 'active';?>"><i class="fas fa-seedling"></i></a>
        <a href="barn.php" class="nav-btn <?php if($activePage==='barn') echo 'active';?>"><i class="fas fa-warehouse"></i></a>
        <a href="mesaje.php" class="nav-btn <?php if($activePage==='mesaje') echo 'active';?>"><i class="fas fa-envelope"></i></a>
        <a href="friends.php" class="nav-btn <?php if($activePage==='friends') echo 'active';?>"><i class="fas fa-user-friends"></i></a>
        <a href="settings.php" class="nav-btn <?php if($activePage==='settings') echo 'active';?>"><i class="fas fa-cog"></i></a>
    </nav>
</div>
<?= $extraJs ?>
</body>
</html>