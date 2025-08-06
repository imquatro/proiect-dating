<?php
session_start(); // PRIMA linie, nimic înainte!
require_once __DIR__ . '/includes/db.php';

// Protecție: dacă nu ești logat, mergi la login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/includes/update_last_active.php';

// 1. Extragere date user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Prima fotografie din galerie servește drept poză de profil
$gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
$profile_photo = 'default-avatar.png';
if (!is_file(__DIR__ . '/' . $profile_photo)) {
    $profile_photo = 'dating/default-avatar.png';
}
if (!empty($gallery)) {
    $candidate = 'dating/uploads/' . $user_id . '/' . $gallery[0];
    if (is_file(__DIR__ . '/' . $candidate)) {
        $profile_photo = $candidate;
    }
}
	
// Verificăm dacă userul este admin
$isAdmin = !empty($user['is_admin']) && $user['is_admin'] == 1;

// 2. Procesare update descriere
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save_desc'])) {
    $desc = trim($_POST['description']);
    $stmt = $db->prepare("UPDATE users SET description=? WHERE id=?");
    $stmt->execute([$desc, $user_id]);
    header("Location: profile.php?desc-updated=1");
    exit;
}

if (isset($_GET['error']) && $_GET['error'] == 'max_photos') {
    echo '<p style="color:red; font-weight:bold; text-align:center;">Ai atins limita maximă de 10 poze.</p>';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilul Meu</title>
        <link rel="stylesheet" href="assets_css/profile.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="main-header">
        <?php if ($isAdmin): ?>
            <a href="admin_panel.php" class="admin-btn" title="Panou Admin" style="margin-right:10px; color:#ffd700; font-size:1.5rem;">
                <i class="fas fa-user-shield"></i>
            </a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" title="Deconectare">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <span class="header-title">Profilul meu</span>
    </div>
    <div class="profile-container">
        <!-- Poză de profil -->
        <div class="profile-gallery">
            <img src="<?= htmlspecialchars($profile_photo) ?>" class="profile-img" alt="Poza de profil">
        </div>
        <!-- Info user -->
        <div class="profile-info-list">
            <div class="info-row"><span class="profile-label">Nume:</span><span class="profile-value"><?=htmlspecialchars($user['username'])?></span></div>
            <div class="info-row"><span class="profile-label">Email:</span><span class="profile-value"><?=htmlspecialchars($user['email'])?></span></div>
            <div class="info-row"><span class="profile-label">Vârstă:</span><span class="profile-value"><?=htmlspecialchars($user['age'])?></span></div>
            <div class="info-row"><span class="profile-label">Sex:</span><span class="profile-value"><?=htmlspecialchars($user['gender'])?></span></div>
            <div class="info-row"><span class="profile-label">Țară:</span><span class="profile-value"><?=htmlspecialchars($user['country'])?></span></div>
            <div class="info-row"><span class="profile-label">Oraș:</span><span class="profile-value"><?=htmlspecialchars($user['city'])?></span></div>
        </div>

        <!-- Descriere editabilă -->
        <div class="desc-edit-wrap">
          <div class="desc-title-row">
              <span style="font-weight:600;color:#7c4dff;">Descriere</span>
              <button type="button" class="desc-action-btn edit" onclick="toggleDescEdit()" id="descEditBtn">
                <i class="fas fa-edit"></i> Editează
              </button>
          </div>
          <div id="desc-view-div" style="display:<?=!empty($user['description']) ? 'block':'none'?>;">
              <div class="desc-field"><?=!empty($user['description']) ? htmlspecialchars($user['description']) : '<span style=\'color:#aaa\'>Fără descriere</span>'?></div>
          </div>
          <form method="POST" style="margin:0;display:<?=empty($user['description']) ? 'block':'none'?>;" id="desc-edit-div">
            <textarea name="description" class="desc-field" maxlength="500"><?=htmlspecialchars($user['description'])?></textarea>
            <button type="submit" class="desc-action-btn" name="save_desc"><i class="fas fa-save"></i> Salvează</button>
          </form>
        </div>

        <!-- Card upload poza -->
		<div class="profile-upload-card">
            <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="upload-photo-form">
                <input type="hidden" name="user_id" value="<?=$user_id?>">
                <!-- Buton 1: Selectează poză -->
                <button type="button" class="profile-upload-btn" id="select-btn">
                    <i class="fas fa-plus-circle"></i> Adaugă poză
                </button>
                <input type="file" name="file" accept="image/*" required id="profile-photo-input" style="display:none;">
                <!-- Buton 2: Încarcă poză -->
                <button type="submit" class="profile-upload-btn" id="upload-btn" style="display:none; margin-left: 16px;">
                    <i class="fas fa-upload"></i> Încarcă poză
                </button>
            </form>
        </div>

        <div class="profile-upload-card">
            <a href="gallery.php" class="profile-upload-btn"><i class="fas fa-images"></i> Vezi galeria</a>
        </div>
    </div>

    <div class="navbar">
        <a class="icon" href="index.php"><i class="fas fa-home"></i></a>
        <a class="icon" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="icon" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="icon active" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
    <script>
      function toggleDescEdit() {
        let editDiv = document.getElementById('desc-edit-div');
        let viewDiv = document.getElementById('desc-view-div');
        editDiv.style.display = (editDiv.style.display==='none'||editDiv.style.display==='') ? 'block':'none';
        viewDiv.style.display = (viewDiv.style.display==='none'||viewDiv.style.display==='') ? 'block':'none';
      }
    </script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectBtn = document.getElementById('select-btn');
    const input = document.getElementById('profile-photo-input');
    const uploadBtn = document.getElementById('upload-btn');

    // La click pe butonul de selectare, deschide inputul de fișier
    selectBtn.addEventListener('click', function() {
        input.click();
    });

    // După selectare, afișează butonul de upload
    input.addEventListener('change', function() {
        if (input.files.length > 0) {
            uploadBtn.style.display = 'inline-block';
        } else {
            uploadBtn.style.display = 'none';
        }
    });
});
</script>
</body>
</html>
