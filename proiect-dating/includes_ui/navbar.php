<nav class="navbar">
    <div class="navbar-left">
        <a href="index.php">Acasă</a>
        <a href="search.php">Căutare</a>
        <a href="matches.php">Meciuri</a>
        <a href="messages.php">Mesaje</a>
        <a href="notifications.php">Notificări</a>
        <a href="settings.php">Setări</a>
        <a href="premium.php">Premium</a>
        <a href="admin.php">Admin</a>
    </div>

    <div class="navbar-right">
        <div class="user-profile">
            <img src="assets/img/user_default.png" alt="Profil" class="profile-picture">
            <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilizator') ?></span>
        </div>
        <a href="logout.php" class="logout-button">Logout</a>
    </div>
</nav>
