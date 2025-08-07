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
        if ($age < 18 || $age > 99) {
            $mesaj = 'Vârsta trebuie să fie între 18 și 99 de ani.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            if ($stmt->fetch()) {
                $mesaj = 'Există deja un cont cu acest email sau nume!';
            } else {
                $stmt = $db->prepare("INSERT INTO users (email, username, password, age, country, city, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$email, $username, $hash, $age, $country, $city, $gender])) {
                    $userId = $db->lastInsertId();
                    $lockedSlots = [1=>1,2=>2,3=>3,4=>4,5=>5];
                    $openSlots = [6,7,8,9,10];
                    foreach ($openSlots as $slot) {
                        $ins = $db->prepare("INSERT INTO user_slots (user_id, slot_number, unlocked, required_level) VALUES (?, ?, 1, 0)");
                        $ins->execute([$userId, $slot]);
                    }
                    foreach ($lockedSlots as $slot => $level) {
                        $ins = $db->prepare("INSERT INTO user_slots (user_id, slot_number, unlocked, required_level) VALUES (?, ?, 0, ?)");
                        $ins->execute([$userId, $slot, $level]);
                    }
                    header('Location: index.php?register=success');
                    exit;
                } else {
                    $mesaj = 'Eroare la înregistrare!';
                }
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
    <title>Înregistrare</title>
    <link rel="stylesheet" href="assets_css/auth.css">
</head>
<body>
    <div class="register-container">
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
            <button type="submit">Înregistrează-te</button>
            <p>Ai deja cont? <a href="login.php">Loghează-te</a></p>
            <?php if ($mesaj): ?>
                <p style="color:#b40b2c; margin-top:10px;"><?= htmlspecialchars($mesaj) ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>