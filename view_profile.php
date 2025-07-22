<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['user_id'])) {
    header("Location: index.php");
    exit;
}
$view_id = (int)$_GET['user_id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$view_id]);
$view_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$view_user) {
    echo "Utilizatorul nu există.";
    exit;
}

$view_poze = [];
if (!empty($view_user['gallery'])) {
    $view_poze = explode(',', $view_user['gallery']);
}
$view_gallery_status = $view_user['gallery_status'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilizator</title>
    <link rel="stylesheet" href="assets_css/view_profile.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="vp-header">
        <a href="index.php" class="vp-btn-back" title="Înapoi la Home">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="vp-header-title">Profil Utilizator</span>
    </div>
    <div class="vp-container">
        <!-- GALERIE CU SĂGEȚI (nu dispare) -->
        <?php if ($view_poze && count($view_poze)>0): ?>
        <div class="vp-gallery">
            <button class="vp-gallery-arrow left" id="vp-arrow-left" type="button"><i class="fas fa-chevron-left"></i></button>
            <?php foreach ($view_poze as $idx=>$src): ?>
                <div class="vp-photo-wrap<?= $view_gallery_status === 'pending' ? ' vp-photo-pending' : '' ?>" id="vp-photo-wrap-<?=$idx?>" style="display:none;">
                    <img src="<?=htmlspecialchars($src)?>" class="vp-profile-img" onclick="openVpLightbox(<?=$idx?>)">
                    <?php if($view_gallery_status === 'pending'): ?>
                        <span class="vp-photo-badge">NEVALIDATĂ</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button class="vp-gallery-arrow right" id="vp-arrow-right" type="button"><i class="fas fa-chevron-right"></i></button>
        </div>
        <?php else: ?>
            <div class="vp-gallery">
                <img src="default-avatar.jpg" alt="Profil" style="display:block;">
            </div>
        <?php endif; ?>

        <!-- Info user -->
        <div class="vp-info-list">
            <div class="vp-info-row"><span class="vp-label">Nume:</span><span class="vp-value"><?=htmlspecialchars($view_user['username'])?></span></div>
            <div class="vp-info-row"><span class="vp-label">Vârstă:</span><span class="vp-value"><?=htmlspecialchars($view_user['age'])?></span></div>
            <div class="vp-info-row"><span class="vp-label">Sex:</span><span class="vp-value"><?=htmlspecialchars($view_user['gender'])?></span></div>
            <div class="vp-info-row"><span class="vp-label">Țară:</span><span class="vp-value"><?=htmlspecialchars($view_user['country'])?></span></div>
            <div class="vp-info-row"><span class="vp-label">Oraș:</span><span class="vp-value"><?=htmlspecialchars($view_user['city'])?></span></div>
        </div>
        <div class="vp-desc-edit-wrap">
            <div class="vp-desc-title-row">
                <span style="font-weight:600;color:#7c4dff;">Descriere</span>
            </div>
            <div id="vp-desc-view-div" style="display:block;">
                <div class="vp-desc-field"><?=!empty($view_user['description']) ? htmlspecialchars($view_user['description']) : '<span style="color:#aaa">Fără descriere</span>'?></div>
            </div>
        </div>
    </div>
    <div class="vp-navbar">
        <a class="vp-icon" href="index.php"><i class="fas fa-home"></i></a>
        <a class="vp-icon" href="matches.php"><i class="fas fa-heart"></i></a>
        <a class="vp-icon" href="messages.php"><i class="fas fa-comments"></i></a>
        <a class="vp-icon" href="profile.php"><i class="fas fa-user"></i></a>
    </div>

    <!-- LIGHTBOX / PANEL pentru POZA MARE + COMENTARII (ascuns implicit) -->
    <div id="vpLightbox" class="vp-lightbox">
        <div class="vp-combo-panel">
            <div class="vp-img-side">
                <button class="vp-arrow vp-arrow-left" id="vpLbPrevBtn">&lt;</button>
                <div class="vp-img-contour">
                    <img id="vpLightboxImg" class="vp-panel-photo" src="" alt="poza mare" />
                </div>
                <button class="vp-arrow vp-arrow-right" id="vpLbNextBtn">&gt;</button>
            </div>
            <div class="vp-comments-side">
                <div class="vp-meta-bar">
                    <span><i class="fas fa-calendar"></i> <span id="vpPhotoDate">2025-06-03</span></span>
                    <span><i class="fas fa-heart"></i> <span id="vpPhotoLikes">0</span></span>
                    <span><i class="fas fa-comment"></i> <span id="vpPhotoCommentsCount">2</span></span>
                </div>
                <div class="vp-comments-wrap" id="vpPhotoComments">
                    <div class="vp-comment-row"><span class="vp-comment-username">alex:</span> <span class="vp-comment-text">Super poză!</span></div>
                    <div class="vp-comment-row"><span class="vp-comment-username">cristi:</span> <span class="vp-comment-text">Frumos loc.</span></div>
                </div>
                <form class="vp-comment-form" id="vpCommentForm" autocomplete="off">
                    <div class="vp-comment-input-row">
                        <img class="vp-comment-avatar" src="default-avatar.jpg" id="vpUserAvatar" />
                        <input type="text" name="comment" placeholder="Adaugă un comentariu..." autocomplete="off" required />
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <button class="vp-lightbox-close" onclick="closeVpLightbox()">&times;</button>
    </div>

    <script>
    // Pozele profilului ca JS array (pt lightbox)
    const vpPhotos = <?= json_encode($view_poze); ?>;
    let vpCurrent = 0;

    // GALERIE NORMALĂ
    function showVpImg(idx) {
        for(let i=0; i<vpPhotos.length; i++) {
            let wrap = document.getElementById('vp-photo-wrap-'+i);
            if(wrap) wrap.style.display = (i===idx) ? 'flex' : 'none';
        }
        document.getElementById('vp-arrow-left').disabled = (idx===0 || vpPhotos.length<=1);
        document.getElementById('vp-arrow-right').disabled = (idx===vpPhotos.length-1 || vpPhotos.length<=1);
        vpCurrent = idx;
    }
    document.getElementById('vp-arrow-left').onclick = function(){ if(vpCurrent>0) showVpImg(vpCurrent-1); }
    document.getElementById('vp-arrow-right').onclick = function(){ if(vpCurrent<vpPhotos.length-1) showVpImg(vpCurrent+1); }
    document.addEventListener('DOMContentLoaded', function(){ if(vpPhotos.length>0) showVpImg(0); });

    // LIGHTBOX panel logic
    let lightboxCurrent = 0;
    function openVpLightbox(idx) {
        if(vpPhotos.length==0) return;
        lightboxCurrent = idx;
        document.getElementById('vpLightboxImg').src = vpPhotos[lightboxCurrent];
        document.getElementById('vpLightbox').style.display = 'flex';
        // aici poți încărca și comentariile dinamic (ajax)
    }
    function closeVpLightbox() {
        document.getElementById('vpLightbox').style.display = 'none';
    }
    document.getElementById('vpLbPrevBtn').onclick = function(){ 
        if(lightboxCurrent>0) openVpLightbox(lightboxCurrent-1);
    }
    document.getElementById('vpLbNextBtn').onclick = function(){ 
        if(lightboxCurrent<vpPhotos.length-1) openVpLightbox(lightboxCurrent+1);
    }
    // ESC pt închidere
    window.addEventListener('keydown', function(e) {
        if (document.getElementById('vpLightbox').style.display=='flex') {
            if (e.key === 'Escape') closeVpLightbox();
            if (e.key === 'ArrowLeft') document.getElementById('vpLbPrevBtn').click();
            if (e.key === 'ArrowRight') document.getElementById('vpLbNextBtn').click();
        }
    });
    </script>
</body>
</html>
