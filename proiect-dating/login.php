<?php
// includes_core
require_once 'includes_core/config.php';
require_once 'includes_core/db.php';
require_once 'includes_core/session.php';
require_once 'includes_core/auth.php';

// Logica înregistrare
require_once 'includes_logic/register.php';

// UI header și navbar
include 'includes_ui/header.php';
include 'includes_ui/navbar.php';
?>

<div class="register-container">
    <h2>Înregistrare</h2>

    <?php if (isset($error)) : ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Nume utilizator" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Parolă" required><br>
        <input type="password" name="confirm_password" placeholder="Confirmare parolă" required><br>
        <button type="submit">Înregistrează-te</button>
    </form>

    <p><a href="login.php">Ai deja cont? Autentifică-te aici.</a></p>
</div>

<?php
// UI footer
include 'includes_ui/footer.php';
?>

<!-- CSS + JS -->
<link rel="stylesheet" href="assets_css/register.css">
<script src="assets_js/main.js"></script>
