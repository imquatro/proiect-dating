<?php
session_start();
require_once __DIR__ . '/includes/db.php';

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_or_email = trim($_POST['user_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($user_or_email && $password) {
        // Caută userul după username sau email
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$user_or_email, $user_or_email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

         if ($user && password_verify($password, $user['password'])) {
            // Parolă corectă, setăm sesiunea
            $_SESSION['user_id'] = $user['id'];
            $upd = $db->prepare('UPDATE users SET last_active = NOW() WHERE id = ?');
            $upd->execute([$user['id']]);
            header('Location: index.php');
            exit;
        } else {
            $mesaj = 'Utilizator sau parolă incorectă.';
        }
    } else {
        $mesaj = 'Completează toate câmpurile.';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Logare - Proiect Dating</title>
    <link rel="stylesheet" href="assets_css/style.css" />
</head>
<body>
    <div class="login-container">
        <form action="login.php" method="POST" class="login-form">
            <h2>Logare</h2>
            <input type="text" name="user_or_email" placeholder="Utilizator sau email" required />
            <input type="password" name="password" placeholder="Parolă" required />
            <button type="submit" class="btn-login">Logare</button>
            <p>Nu ai cont? <a href="register.php">Înregistrează-te</a></p>
            <?php if ($mesaj): ?>
                <p style="color: #b40b2c; margin-top: 10px;"><?= htmlspecialchars($mesaj) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
