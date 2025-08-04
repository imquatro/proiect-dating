<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/includes/update_last_active.php';

// Preluăm datele userului inclusiv statusul de admin
$stmt = $db->prepare('SELECT gallery, gallery_status, is_admin FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$poze = $user['gallery'] ? explode(',', $user['gallery']) : [];
$statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
$isAdmin = !empty($user['is_admin']) && $user['is_admin'] == 1;

// Procesează acțiunile de setare sau ștergere
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo'], $_POST['action'])) {
    $photo = basename($_POST['photo']);
    $index = array_search($photo, $poze);
    if ($index !== false) {
        if ($_POST['action'] === 'set_profile') {
            // mută fotografia selectată pe prima poziție
            $chosenPhoto = $poze[$index];
            $chosenStatus = $statuses[$index] ?? '';
            array_splice($poze, $index, 1);
            if (isset($statuses[$index])) array_splice($statuses, $index, 1);
            array_unshift($poze, $chosenPhoto);
            array_unshift($statuses, $chosenStatus);
            $stmtUpd = $db->prepare('UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?');
            $stmtUpd->execute([implode(',', $poze), implode(',', $statuses), $user_id]);
        } elseif ($_POST['action'] === 'delete') {
            $filePath = 'uploads/' . $user_id . '/' . $photo;
            if (is_file($filePath)) {
                unlink($filePath);
            }
            unset($poze[$index]);
            if (isset($statuses[$index])) unset($statuses[$index]);
            $poze = array_values($poze);
            $statuses = array_values($statuses);
            $stmtUpd = $db->prepare('UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?');
            $stmtUpd->execute([implode(',', $poze), implode(',', $statuses), $user_id]);
        }
    }
    header('Location: gallery.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria Mea</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="assets_css/nav.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
      .gallery-btns { display:flex; gap:10px; justify-content:center; margin-top:10px; }
    </style>
</head>
<body>
    <div class="main-header">
        <?php if ($isAdmin): ?>
            <a href="admin_panel.php" class="admin-btn" title="Panou Admin">
                <i class="fas fa-user-shield"></i>
            </a>
        <?php else: ?>
            <span style="width:38px;"></span>
        <?php endif; ?>
        <span class="header-title">Galeria mea</span>
        <a href="profile.php" class="logout-btn" title="Înapoi"><i class="fas fa-arrow-left"></i></a>
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
        <a class="icon msg-icon" href="messages.php"><i class="fas fa-comments"></i><span class="nav-msg-dot" id="msgAlert"></span></a>
        <a class="icon active" href="profile.php"><i class="fas fa-user"></i></a>
    </div>
    <script src="assets_js/nav.js"></script>
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