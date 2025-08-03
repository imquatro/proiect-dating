<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesează acțiunile de setare sau ștergere
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'], $_POST['action'])) {
    $photo = basename($_POST['photo']);
    $stmt = $db->prepare('SELECT gallery, gallery_status FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $gallery = $user['gallery'] ? explode(',', $user['gallery']) : [];
    $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
    $index = array_search($photo, $gallery);
    if ($index !== false) {
        if ($_POST['action'] === 'set_profile') {
            // mută fotografia selectată pe prima poziție
            $chosenPhoto = $gallery[$index];
            $chosenStatus = $statuses[$index] ?? '';
            array_splice($gallery, $index, 1);
            if (isset($statuses[$index])) array_splice($statuses, $index, 1);
            array_unshift($gallery, $chosenPhoto);
            array_unshift($statuses, $chosenStatus);
            $stmtUpd = $db->prepare('UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?');
            $stmtUpd->execute([implode(',', $gallery), implode(',', $statuses), $user_id]);
        } elseif ($_POST['action'] === 'delete') {
            $filePath = 'uploads/' . $user_id . '/' . $photo;
            if (is_file($filePath)) {
                unlink($filePath);
            }
            unset($gallery[$index]);
            if (isset($statuses[$index])) unset($statuses[$index]);
            $gallery = array_values($gallery);
            $statuses = array_values($statuses);
            $stmtUpd = $db->prepare('UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?');
            $stmtUpd->execute([implode(',', $gallery), implode(',', $statuses), $user_id]);
        }
    }
    header('Location: gallery.php');
    exit;
}

// Reîncarcă datele pentru afișare
$stmt = $db->prepare('SELECT gallery, gallery_status FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$poze = $user['gallery'] ? explode(',', $user['gallery']) : [];
$statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria Mea</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
      .gallery-btns { display:flex; gap:10px; justify-content:center; margin-top:10px; }
    </style>
</head>
<body>
    <div class="main-header">
        <a href="profile.php" class="logout-btn" title="Înapoi"><i class="fas fa-arrow-left"></i></a>
        <span class="header-title">Galeria mea</span>
    </div>
    <div class="profile-container">
        <?php if ($poze && count($poze) > 0): ?>
        <div class="profile-gallery">
            <button class="gallery-arrow left" id="arrow-left" onclick="prevImg(<?=count($poze)?>)" type="button"><i class="fas fa-chevron-left"></i></button>
            <?php foreach ($poze as $idx => $src): ?>
                <?php $status = $statuses[$idx] ?? ''; ?>
                <div class="photo-wrap<?= $status === 'pending' ? ' photo-pending' : '' ?>" id="photo-wrap-<?=$idx?>" style="display:none; flex-direction:column; align-items:center;">
                    <img src="<?= 'uploads/' . $user_id . '/' . htmlspecialchars($src) ?>" class="profile-img" alt="poza">
                    <?php if ($status === 'pending'): ?>
                        <span class="photo-badge">NEVALIDATĂ</span>
                    <?php endif; ?>
                    <div class="gallery-btns">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="photo" value="<?= htmlspecialchars($src) ?>">
                            <button type="submit" name="action" value="set_profile" class="profile-upload-btn"><i class="fas fa-user"></i> Setează profil</button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Ștergi această poză?');">
                            <input type="hidden" name="photo" value="<?= htmlspecialchars($src) ?>">
                            <button type="submit" name="action" value="delete" class="profile-upload-btn"><i class="fas fa-trash"></i> Șterge</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <button class="gallery-arrow right" id="arrow-right" onclick="nextImg(<?=count($poze)?>)" type="button"><i class="fas fa-chevron-right"></i></button>
        </div>
        <?php else: ?>
            <div class="profile-gallery">
                <img src="img/user_default.png" alt="Fără poze" style="display:block;">
            </div>
        <?php endif; ?>
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
        for (let i = 0; i < total; i++) {
          let wrap = document.getElementById('photo-wrap-' + i);
          if (wrap) wrap.style.display = (i === idx) ? 'flex' : 'none';
        }
        document.getElementById('arrow-left').disabled = (idx === 0 || total <= 1);
        document.getElementById('arrow-right').disabled = (idx === total - 1 || total <= 1);
        window.currentImg = idx;
      }
      function prevImg(total) { if (window.currentImg > 0) showImg(window.currentImg - 1, total); }
      function nextImg(total) { if (window.currentImg < total - 1) showImg(window.currentImg + 1, total); }
      document.addEventListener('DOMContentLoaded', function(){
        window.currentImg = 0;
        let total = document.querySelectorAll('.photo-wrap').length;
        if (total > 0) showImg(0, total);
      });
    </script>
</body>
</html>