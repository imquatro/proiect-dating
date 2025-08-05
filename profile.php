<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $db->prepare('SELECT username, email, age, country, city, gender FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$activePage = 'profile';
ob_start();
?>
<div class="profile">
    <p><strong>Nume:</strong> <?= htmlspecialchars($user['username'] ?? '') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
    <p><strong>Vârstă:</strong> <?= htmlspecialchars($user['age'] ?? '') ?></p>
    <p><strong>Sex:</strong> <?= htmlspecialchars($user['gender'] ?? '') ?></p>
    <p><strong>Țară:</strong> <?= htmlspecialchars($user['country'] ?? '') ?></p>
    <p><strong>Oraș:</strong> <?= htmlspecialchars($user['city'] ?? '') ?></p>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/profile.css';
include 'template.php';