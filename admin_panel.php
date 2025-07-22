<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificare admin
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$isAdmin = $stmt->fetchColumn();

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

// Procesare aprobare/respinge poza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'], $_POST['photo_index'])) {
    $action = $_POST['action'];
    $targetUserId = (int)$_POST['user_id'];
    $photoIndex = (int)$_POST['photo_index'];

    // Preluare galeria utilizatorului țintă
    $stmt = $db->prepare("SELECT gallery FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $gallery = $stmt->fetchColumn();
    $photos = $gallery ? explode(',', $gallery) : [];

    if (isset($photos[$photoIndex])) {
        unset($photos[$photoIndex]);
        $photos = array_values($photos);
        $newGallery = implode(',', $photos);

        if ($action === 'approve') {
            $newStatus = count($photos) === 0 ? 'approved' : 'pending';
            $stmt = $db->prepare("UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?");
            $stmt->execute([$newGallery, $newStatus, $targetUserId]);
        } elseif ($action === 'reject') {
            $newStatus = count($photos) === 0 ? 'none' : 'pending';
            $stmt = $db->prepare("UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?");
            $stmt->execute([$newGallery, $newStatus, $targetUserId]);
        }
    }

    header("Location: admin_panel.php?msg=Acțiune efectuată cu succes!");
    exit;
}

// Preluare utilizatori cu galerii în status pending
$stmt = $db->prepare("SELECT id, username, gallery, gallery_status FROM users WHERE gallery IS NOT NULL AND gallery <> '' AND gallery_status = 'pending'");
$stmt->execute();
$usersWithPhotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

function explodePhotos($gallery) {
    return $gallery ? explode(',', $gallery) : [];
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Panou Admin - Proiect Dating</title>
<link rel="stylesheet" href="assets_css/profile.css" />
<link rel="stylesheet" href="assets_css/photo-validate.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
<div class="main-header">
    <a href="logout.php" class="logout-btn" title="Deconectare"><i class="fas fa-sign-out-alt"></i></a>
    <span class="header-title">Panou Admin</span>
</div>
<div class="profile-container">
    <h1 style="text-align:center; margin-bottom:24px; color:#7c4dff;">Validare Poze Utilizatori</h1>
    <?php
    if (!empty($usersWithPhotos)) {
        $user = $usersWithPhotos[0];
        $photos = explodePhotos($user['gallery']);
    ?>
    <div class="photo-validate-container" id="photoValidateBox">
        <button class="nav-btn" id="prevBtn" title="Poza anterioară">&#8592;</button>
        <div class="photo-validate-inner">
            <img src="<?= htmlspecialchars($photos[0] ?? '') ?>" alt="poza de validat" class="photo-preview" id="photoPreview" onclick="openLightbox()" />
            <form method="POST" id="validateForm">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <input type="hidden" name="photo_index" id="photoIndexInput" value="0">
                <div class="validate-actions">
                    <button type="submit" name="action" value="approve" class="validate-btn">Aprobă</button>
                    <button type="submit" name="action" value="reject" class="reject-btn">Respinge</button>
                </div>
            </form>
        </div>
        <button class="nav-btn" id="nextBtn" title="Poza următoare">&#8594;</button>
    </div>
    <!-- Lightbox pentru poza mare și butoane pe poză -->
    <div class="lightbox" id="lightbox">
        <button class="lb-nav" id="lbPrevBtn">&#8592;</button>
        <div class="lightbox-img-wrap">
            <img src="<?= htmlspecialchars($photos[0] ?? '') ?>" alt="poza mărită" class="lightbox-img" id="lightboxImg" />
            <div class="lightbox-actions-on-img">
                <button type="button" class="validate-btn" id="lbApproveBtn"><i class="fas fa-check"></i></button>
                <button type="button" class="reject-btn" id="lbRejectBtn"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <button class="lb-nav" id="lbNextBtn">&#8594;</button>
        <button class="close-lightbox" onclick="closeLightbox()">&times;</button>
    </div>
    <script>
        const photos = <?php echo json_encode($photos); ?>;
    </script>
    <script src="assets_js/admin_photos.js"></script>
    <?php
    } else {
        echo "<div style='text-align:center; margin-top:32px;'>Nu există poze de validat în acest moment.</div>";
    }
    ?>
</div>
<div class="navbar">
    <a class="icon" href="index.php"><i class="fas fa-home"></i></a>
    <a class="icon" href="matches.php"><i class="fas fa-heart"></i></a>
    <a class="icon" href="messages.php"><i class="fas fa-comments"></i></a>
    <a class="icon active" href="profile.php"><i class="fas fa-user"></i></a>
</div>
</body>
</html>
