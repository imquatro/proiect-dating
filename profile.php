<?php
session_start(); // PRIMA linie, nimic înainte!
require_once __DIR__ . '/includes/db.php';

// Protecție: dacă nu ești logat, mergi la login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// 1. Extragere date user + poze
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$poze = [];
if (!empty($user['gallery'])) {
    $poze = explode(',', $user['gallery']);
}

// 2. Procesare update descriere
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save_desc'])) {
    $desc = trim($_POST['description']);
    $stmt = $db->prepare("UPDATE users SET description=? WHERE id=?");
    $stmt->execute([$desc, $user_id]);
    header("Location: profile.php?desc-updated=1");
    exit;
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
        <a href="logout.php" class="logout-btn" title="Deconectare">
            <i class="fas fa-sign-out-alt"></i>
        </a>
        <span class="header-title">Profilul meu</span>
    </div>
    <div class="profile-container">
        <!-- Galerie poze cu săgeți -->
        <?php if ($poze && count($poze)>0): ?>
        <div class="profile-gallery">
            <button class="gallery-arrow left" id="arrow-left" onclick="prevImg(<?=count($poze)?>)" type="button"><i class="fas fa-chevron-left"></i></button>
            <?php foreach ($poze as $idx=>$src): ?>
                <img src="<?=htmlspecialchars($src)?>" id="gallery-img-<?=$idx?>" style="display:none;">
            <?php endforeach; ?>
            <button class="gallery-arrow right" id="arrow-right" onclick="nextImg(<?=count($poze)?>)" type="button"><i class="fas fa-chevron-right"></i></button>
        </div>
        <?php else: ?>
            <div class="profile-gallery">
                <img src="default-avatar.jpg" alt="Profil" style="display:block;">
            </div>
        <?php endif; ?>

        <!-- Info user -->
        <div class="profile-info-list">
            <div class="info-row"><span class="profile-label">Nume:</span><span class="profile-value"><?=$user['username']?></span></div>
            <div class="info-row"><span class="profile-label">Email:</span><span class="profile-value"><?=$user['email']?></span></div>
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
            <i class="fas fa-plus-circle"></i> Adauga poză
        </button>
        <input type="file" name="profile_photo" accept="image/*" required id="profile-photo-input" style="display:none;">
        <!-- Buton 2: Încarcă poză -->
        <button type="submit" class="profile-upload-btn" id="upload-btn" style="display:none; margin-left: 16px;">
            <i class="fas fa-upload"></i> Încarcă poză
        </button>
    </form>
</div>

    <div class="navbar">
        <a class="icon" href="index.php"><i class="fas fa-home"></i></a>
        <a class="icon" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="icon" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="icon active" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
    <script>
      let currentImg = 0;
      function showImg(idx, total) {
        for(let i=0; i<total; i++) {
          document.getElementById('gallery-img-'+i).style.display = (i===idx) ? 'block' : 'none';
        }
        document.getElementById('arrow-left').disabled = idx===0;
        document.getElementById('arrow-right').disabled = idx===total-1;
        window.currentImg = idx;
      }
      function prevImg(total) {
        if(window.currentImg>0) showImg(window.currentImg-1, total);
      }
      function nextImg(total) {
        if(window.currentImg<total-1) showImg(window.currentImg+1, total);
      }
      document.addEventListener("DOMContentLoaded", function(){
        window.currentImg = 0;
        let total = document.querySelectorAll('.profile-gallery img').length;
        if(total>0) showImg(0, total);
      });
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
