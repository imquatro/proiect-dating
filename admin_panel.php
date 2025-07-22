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

    $stmt = $db->prepare("SELECT gallery FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $gallery = $stmt->fetchColumn();
    $photos = $gallery ? explode(',', $gallery) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'], $_POST['photo_index'])) {
    $action = $_POST['action'];
    $targetUserId = (int)$_POST['user_id'];
    $photoIndex = (int)$_POST['photo_index'];

    $stmt = $db->prepare("SELECT gallery FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $gallery = $stmt->fetchColumn();
    $photos = $gallery ? explode(',', $gallery) : [];

    if (isset($photos[$photoIndex])) {
        if ($action === 'approve') {
            // NU MAI ȘTERGE poza, doar setează statusul ca "approved"
            $newStatus = 'approved';
            $stmt = $db->prepare("UPDATE users SET gallery_status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $targetUserId]);
        } elseif ($action === 'reject') {
            // DOAR LA REJECT SE ȘTERGE poza
            unset($photos[$photoIndex]);
            $photos = array_values($photos);
            $newGallery = implode(',', $photos);
            $newStatus = count($photos) === 0 ? 'none' : 'pending';
            $stmt = $db->prepare("UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?");
            $stmt->execute([$newGallery, $newStatus, $targetUserId]);
        }
    }

    header("Location: admin_panel.php?msg=Acțiune efectuată cu succes!");
    exit;
}


    header("Location: admin_panel.php?msg=Acțiune efectuată cu succes!");
    exit;
}

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
<link rel="stylesheet" href="assets_css/admin.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body>
<div class="admin-header">
    <a href="logout.php" class="admin-logout-btn" title="Deconectare"><i class="fas fa-sign-out-alt"></i></a>
    <span class="admin-header-title">Panou Admin</span>
</div>
<div class="admin-container">
    <h1 class="admin-title">Validare Poze Utilizatori</h1>
    <?php
    if (!empty($usersWithPhotos)) {
        $user = $usersWithPhotos[0];
        $photos = explodePhotos($user['gallery']);
    ?>
    <div class="admin-photo-validate-box">
        <button class="admin-nav-btn" id="adminPrevBtn" title="Poza anterioară">&#8592;</button>
        <div class="admin-photo-inner">
            <img src="<?= htmlspecialchars($photos[0] ?? '') ?>" alt="poza de validat" class="admin-photo-img" id="adminPhotoPreview" onclick="openAdminLightbox()" />
            <form method="POST" id="adminValidateForm">
                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                <input type="hidden" name="photo_index" id="adminPhotoIndexInput" value="0">
                <div class="admin-validate-actions">
                    <button type="submit" name="action" value="approve" class="admin-validate-btn">Aprobă</button>
                    <button type="submit" name="action" value="reject" class="admin-reject-btn">Respinge</button>
                </div>
            </form>
        </div>
        <button class="admin-nav-btn" id="adminNextBtn" title="Poza următoare">&#8594;</button>
    </div>
    <div class="admin-lightbox" id="adminLightbox">
        <button class="admin-lb-nav" id="adminLbPrevBtn">&#8592;</button>
        <div class="admin-lightbox-img-wrap">
            <img src="<?= htmlspecialchars($photos[0] ?? '') ?>" alt="poza mărită" class="admin-lightbox-img" id="adminLightboxImg" />
            <div class="admin-lightbox-actions-on-img">
                <button type="button" class="admin-validate-btn" id="adminLbApproveBtn"><i class="fas fa-check"></i></button>
                <button type="button" class="admin-reject-btn" id="adminLbRejectBtn"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <button class="admin-lb-nav" id="adminLbNextBtn">&#8594;</button>
        <button class="admin-close-lightbox" onclick="closeAdminLightbox()">&times;</button>
    </div>
    <script>
        const adminPhotos = <?php echo json_encode($photos); ?>;
    </script>
    <script src="assets_js/admin_photos.js"></script>
    <?php
    } else {
        echo "<div style='text-align:center; margin-top:32px;'>Nu există poze de validat în acest moment.</div>";
    }
    ?>
</div>
<div class="admin-navbar">
    <a class="admin-icon" href="index.php"><i class="fas fa-home"></i></a>
    <a class="admin-icon" href="matches.php"><i class="fas fa-heart"></i></a>
    <a class="admin-icon" href="messages.php"><i class="fas fa-comments"></i></a>
    <a class="admin-icon active" href="profile.php"><i class="fas fa-user"></i></a>
</div>
</body>
</html>
