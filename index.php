<?php
session_start();
// Poți adăuga aici verificare sesiune dacă e nevoie
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acasă</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- HEADER -->
    <div class="main-header">
        <a href="logout.php" class="logout-btn" title="Deconectare">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <span class="header-title">HOME PAGE</span>
    </div>
    <!-- CONTAINER ALB CENTRAT -->
    <div class="profile-container">
        <!-- Aici poți pune orice conținut vrei, momentan este gol -->
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
