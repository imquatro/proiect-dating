<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dating</title>
    <link rel="stylesheet" href="assets_css/layout.css">
    <?php if ($page === 'home'): ?>
        <link rel="stylesheet" href="assets_css/mini-profile.css">
        <link rel="stylesheet" href="assets_css/farm-slots.css">
    <?php elseif ($page === 'matches'): ?>
        <link rel="stylesheet" href="assets_css/matches.css">
    <?php elseif ($page === 'messages'): ?>
        <link rel="stylesheet" href="assets_css/messages.css">
    <?php elseif ($page === 'profile'): ?>
        <link rel="stylesheet" href="assets_css/profile.css">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
<div class="main-header">
    <?php if ($isAdmin): ?>
        <a href="admin_panel.php" class="admin-btn" title="Panou Admin"><i class="fas fa-user-shield"></i></a>
    <?php else: ?>
        <span style="width:38px;"></span>
    <?php endif; ?>
    <span class="header-title"></span>
    <a href="logout.php" class="logout-btn" title="Deconectare"><i class="fas fa-sign-out-alt"></i></a>
</div>
<div class="profile-container">
    <?php include $pageFile; ?>
</div>
<div class="navbar">
    <a class="nav-btn <?php if($page==='home') echo 'active'; ?>" href="?page=home">Home</a>
    <a class="nav-btn <?php if($page==='matches') echo 'active'; ?>" href="?page=matches">Matches</a>
    <a class="nav-btn <?php if($page==='messages') echo 'active'; ?>" href="?page=messages">Messages</a>
    <a class="nav-btn <?php if($page==='profile') echo 'active'; ?>" href="?page=profile">Profile</a>
</div>
</body>
</html>