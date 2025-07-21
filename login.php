<?php
require_once __DIR__ . '/includes/db.php';
// orice alt cod PHP de procesare login...
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logare - Proiect Dating</title>
    <link rel="stylesheet" href="assets_css/style.css">
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST" class="login-form">
            <h2>Logare</h2>
            <input type="text" name="user_or_email" placeholder="Utilizator sau email" required>
            <input type="password" name="password" placeholder="Parolă" required>
            <button type="submit" class="btn-login">Logare</button>
            <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            session_start();
            $_SESSION['user'] = $_POST['user_or_email'];
            header('Location: index.php');
            exit();
        }
        ?>
    </div>
</body>
</html>
