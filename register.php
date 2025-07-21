<?php
require_once __DIR__ . '/includes/db.php';

$mesaj = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $age = isset($_POST['age']) ? intval($_POST['age']) : null;
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $gender = $_POST['gender'] ?? '';

    if ($email && $username && $password && $age && $country && $city && $gender) {
        // Criptare parolă
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Verifică dacă email sau username există deja
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $mesaj = 'Există deja un cont cu acest email sau nume!';
        } else {
            // Inserare user nou cu câmpuri suplimentare
            $stmt = $db->prepare("INSERT INTO users (email, username, password, age, country, city, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$email, $username, $hash, $age, $country, $city, $gender])) {
                header('Location: login.php?register=success');
                exit;
            } else {
                $mesaj = 'Eroare la înregistrare!';
            }
        }
    } else {
        $mesaj = 'Completează toate câmpurile!';
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare - Proiect Dating</title>
    <link rel="stylesheet" href="assets_css/style.css">
</head>
<body>
    <div class="login-container">
        <form action="register.php" method="POST" class="login-form">
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
            <p>Ai deja cont? <a href="login.php">Loghează-te</a></p>
            <?php if ($mesaj): ?>
                <p style="color:#b40b2c; margin-top:10px;"><?= htmlspecialchars($mesaj) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
