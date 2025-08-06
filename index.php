<?php
$registered = isset($_GET['register']) && $_GET['register'] === 'success';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Bun venit</title>
    <link rel="stylesheet" href="assets_css/index.css">
    <link rel="stylesheet" href="assets_css/auth.css">
</head>
<body>
    <div class="app-frame">
        <?php if (!$registered): ?>
        <div id="welcome-frame">
            <p>WELCOME TO FARMING COMMUNITY!</p>
            <button id="start-button">Start</button>
        </div>
        <?php endif; ?>
        <div id="auth-container" class="<?= $registered ? '' : 'hidden' ?>">
            <div class="login-container">
                <?php if ($registered): ?>
                <div id="register-msg" class="register-msg">Cont creat cu succes!</div>
                <?php endif; ?>
                <form id="login-form" class="login-form" action="login.php" method="POST">
                    <h2>Logare</h2>
                    <input type="text" name="user_or_email" placeholder="Utilizator sau email" required />
                    <input type="password" name="password" placeholder="Parolă" required />
                    <button type="submit" class="btn-login">Logare</button>
                    <p>Nu ai cont? <a href="#" id="show-register">Înregistrează-te</a></p>
                </form>
                <form id="register-form" class="login-form hidden" action="register.php" method="POST">
                    <h2>Înregistrare</h2>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="username" placeholder="Nume utilizator" required>
                    <input type="password" name="password" placeholder="Parolă" required>
                    <input type="number" name="age" placeholder="Vârstă" min="18" max="99" required>
                    <input type="text" name="country" placeholder="Țară" required>
                    <input type="text" name="city" placeholder="Oraș" required>
                    <select name="gender" required>
                        <option value="">Alege sexul</option>
                        <option value="masculin">Masculin</option>
                        <option value="feminin">Feminin</option>
                    </select>
                    <button type="submit" class="btn-login">Înregistrează-te</button>
                    <p>Ai deja cont? <a href="#" id="show-login">Loghează-te</a></p>
                </form>
            </div>
        </div>
    </div>
    <script src="assets_js/index.js"></script>
</body>
</html>