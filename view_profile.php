<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificăm dacă utilizatorul curent este admin
$stmtAdmin = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
$stmtAdmin->execute([$user_id]);
$isAdmin = $stmtAdmin->fetchColumn();

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

$comments = [
    ['username'=>'alex', 'text'=>'Super poză!'],
    ['username'=>'cristi', 'text'=>'Frumos loc.']
];
$likes = 0;
$data_pozei = '2025-06-03'; // Exemplu
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Utilizator</title>
    <link rel="stylesheet" href="assets_css/profile.css">
    <link rel="stylesheet" href="assets_css/view_profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
        <div class="vp-header">
        <?php if ($isAdmin): ?>
            <a href="admin_panel.php" class="admin-btn" title="Panou Admin"><i class="fas fa-user-shield"></i></a>
        <?php endif; ?>
        <a href="index.php" class="vp-btn-back" title="Înapoi la Home">
            <i class="fas fa-arrow-left"></i>
        </a>
        <span class="vp-header-title">Profil Utilizator</span>
		</div>
    <div class="vp-container">

        <!-- Galerie poze cu săgeți -->
        <?php if ($view_poze && count($view_poze) > 0): ?>
        <div class="vp-gallery">
            <button class="vp-gallery-arrow left" id="vp-arrow-left" onclick="vpPrevImg(<?=count($view_poze)?>)" type="button"><i class="fas fa-chevron-left"></i></button>
            <?php foreach ($view_poze as $idx=>$src): ?>
                <div class="vp-photo-wrap<?= $view_gallery_status === 'pending' ? ' vp-photo-pending' : '' ?>"
                     id="vp-photo-wrap-<?=$idx?>"
                     style="<?= $idx === 0 ? 'display:flex;' : 'display:none;' ?>">
                    <img src="<?=htmlspecialchars($src)?>" class="vp-profile-img" onclick="openSuperPanel(<?=$idx?>)">
                    <?php if($view_gallery_status === 'pending'): ?>
                        <span class="vp-photo-badge">NEVALIDATĂ</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button class="vp-gallery-arrow right" id="vp-arrow-right" onclick="vpNextImg(<?=count($view_poze)?>)" type="button"><i class="fas fa-chevron-right"></i></button>
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

        <!-- Doar vizualizare descriere -->
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

    <!-- SUPERPANEL Lightbox (initial ascuns) -->
    <div class="vp-combo-panel-overlay" id="vpComboPanelOverlay" style="display:none;">
        <div class="vp-combo-panel">
            <div class="vp-img-side">
                <button class="vp-arrow vp-arrow-left" onclick="vpLightboxPrev()"><i class="fas fa-chevron-left"></i></button>
                <div class="vp-img-contour">
                    <img id="vpPanelPhoto" src="" class="vp-panel-photo" alt="poza mare" />
                </div>
                <button class="vp-arrow vp-arrow-right" onclick="vpLightboxNext()"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="vp-comments-side">
                <div class="vp-meta-bar">
                    <span><i class="fas fa-calendar"></i> <span id="vpMetaDate"></span></span>
                    <span><i class="fas fa-heart"></i> <span id="vpMetaLikes">0</span></span>
                    <span><i class="fas fa-comment"></i> <span id="vpMetaComments">0</span></span>
                    <button class="vp-close-btn" onclick="vpCloseComboPanel()">&times;</button>
                </div>
                <div class="vp-comments-wrap" id="vpCommentsWrap"></div>
                <form class="vp-comment-form" id="vpCommentForm" autocomplete="off">
                    <div class="vp-comment-input-row">
                        <img class="vp-comment-avatar" src="default-avatar.jpg" id="vpUserAvatar" />
                        <input type="text" name="comment" placeholder="Adaugă comentariu..." required />
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TOT JS-UL AICI, FUNCȚIONAL! -->
    <script>
    // ===================== GALERIE MICĂ (slider profil) =====================
    let vpCurrentImg = 0;

    function showVpImg(idx, total) {
        for (let i = 0; i < total; i++) {
            let wrap = document.getElementById('vp-photo-wrap-' + i);
            if (wrap) wrap.style.display = (i === idx) ? 'flex' : 'none';
        }
        document.getElementById('vp-arrow-left').disabled = (idx === 0 || total <= 1);
        document.getElementById('vp-arrow-right').disabled = (idx === total - 1 || total <= 1);
        window.vpCurrentImg = idx;
    }

    function vpPrevImg(total) {
        if (window.vpCurrentImg > 0) showVpImg(window.vpCurrentImg - 1, total);
    }

    function vpNextImg(total) {
        if (window.vpCurrentImg < total - 1) showVpImg(window.vpCurrentImg + 1, total);
    }

    document.addEventListener("DOMContentLoaded", function () {
        window.vpCurrentImg = 0;
        let total = document.querySelectorAll('.vp-photo-wrap').length;
        if (total > 0) showVpImg(0, total);
    });

    // ========== LIGHTBOX (panou mare cu imagine + comentarii jos) ===========
    const superPanelImages = <?=json_encode($view_poze)?>;
    const superPanelComments = <?=json_encode($comments)?>;
    const superPanelDate = "<?=htmlspecialchars($data_pozei)?>";
    const superPanelLikes = <?=$likes?>;
    let superPanelIdx = 0;

    function openSuperPanel(idx) {
        superPanelIdx = idx;
        updateSuperPanel();
        document.getElementById('vpComboPanelOverlay').style.display = 'flex';
    }

    function vpCloseComboPanel() {
        document.getElementById('vpComboPanelOverlay').style.display = 'none';
    }

    function vpLightboxPrev() {
        if (superPanelIdx > 0) {
            superPanelIdx--;
            updateSuperPanel();
        }
    }

    function vpLightboxNext() {
        if (superPanelIdx < superPanelImages.length - 1) {
            superPanelIdx++;
            updateSuperPanel();
        }
    }

    function updateSuperPanel() {
        document.getElementById('vpPanelPhoto').src = superPanelImages[superPanelIdx] ?? 'default-avatar.jpg';
        document.getElementById('vpMetaDate').textContent = superPanelDate;
        document.getElementById('vpMetaLikes').textContent = superPanelLikes;
        document.getElementById('vpMetaComments').textContent = superPanelComments.length;
        let cdiv = document.getElementById('vpCommentsWrap');
        cdiv.innerHTML = '';
        for (let c of superPanelComments) {
            cdiv.innerHTML += `<div class="vp-comment-row"><span class="vp-comment-username">${c.username}:</span><span class="vp-comment-text">${c.text}</span></div>`;
        }
    }

    document.getElementById('vpComboPanelOverlay').addEventListener('click', function(e){
        if(e.target === this) vpCloseComboPanel();
    });
    </script>
</body>
</html>
