<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Redirecționează la login dacă nu există sesiune activă
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Preluare informații utilizator și galerie + status admin
$stmt = $db->prepare('SELECT username, gallery, is_admin FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = !empty($user['is_admin']) && $user['is_admin'] == 1;

// Prima imagine din galerie este folosită ca avatar
$gallery = !empty($user['gallery']) ? explode(',', $user['gallery']) : [];
$mini_avatar = !empty($gallery)
    ? 'uploads/' . $user_id . '/' . $gallery[0]
    : 'img/user_default.png';

$user_name = $user['username'] ?? ($_SESSION['username'] ?? 'UserName');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acasă</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="assets_css/mini-profile.css">
    <link rel="stylesheet" href="assets_css/farm-slots.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- HEADER -->
        <div class="main-header">
        <?php if ($isAdmin): ?>
            <a href="admin_panel.php" class="admin-btn" title="Panou Admin">
                <i class="fas fa-user-shield"></i>
            </a>
        <?php else: ?>
            <span style="width:38px;"></span>
        <?php endif; ?>
        <span class="header-title">HOME PAGE</span>
        <a href="logout.php" class="logout-btn" title="Deconectare">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
    <!-- CONTAINER ALB CENTRAT -->
    <div class="profile-container">
                <!-- MINI PROFIL DINAMIC -->
        <div class="mini-profile">
            <img src="<?= htmlspecialchars($mini_avatar) ?>" alt="Avatar" class="mini-profile-avatar" />
            <div class="mini-profile-info">
                <div class="mini-profile-username"><?= htmlspecialchars($user_name) ?></div>
                <div class="mini-profile-stats">
                    <span>Level: 10</span> | <span>XP: 1500</span>
                </div>
            </div>
        </div>
        <!-- LINIE DE SEPARARE -->
        <hr class="farm-divider">
        <!-- GRID DE SLOTURI (5 pe linie, total 30) -->
        <div class="farm-slots">
            <?php
            $total_slots = 30;
            $slots_per_row = 5;
            for ($i = 0; $i < $total_slots; $i++) {
                if ($i % $slots_per_row === 0) echo '<div class="farm-row">';
                echo '<div class="farm-slot"></div>';
                if ($i % $slots_per_row === $slots_per_row - 1) echo '</div>';
            }
            if ($total_slots % $slots_per_row !== 0) echo '</div>';
            ?>
        </div>
    </div>
    <!-- NAVBAR -->
    <div class="navbar">
        <a class="icon active" href="index.php"><i class="fas fa-home"></i></a>
        <a class="icon" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="icon" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="icon" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
</body>
</html>
