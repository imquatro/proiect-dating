<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/includes/update_last_active.php';
// Verificare admin
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$isAdmin = $stmt->fetchColumn();

if (!$isAdmin) {
    header("Location: index.php");
    exit;
}

// Procesare aprobare/respinge poza
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['user_id'], $_POST['photo_name'])) {
    $action = $_POST['action'];
    $targetUserId = (int)$_POST['user_id'];
    $photoName = $_POST['photo_name'];

    // Ia galeria și statusul real pentru user
    $stmt = $db->prepare("SELECT gallery, gallery_status FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $photos = $user['gallery'] ? explode(',', $user['gallery']) : [];
    $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];

    // CAUTĂ INDEXUL în galeria actuală (nu din pending!)
    $foundIndex = array_search($photoName, $photos);

    if ($foundIndex !== false) {
        if ($action === 'approve') {
            $statuses[$foundIndex] = 'approved';
        } elseif ($action === 'reject') {
            unset($photos[$foundIndex]);
            unset($statuses[$foundIndex]);
            $photos = array_values($photos);
            $statuses = array_values($statuses);
        }
        $newGallery = implode(',', $photos);
        $newStatus = implode(',', $statuses);
        $stmt = $db->prepare("UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?");
        $stmt->execute([$newGallery, $newStatus, $targetUserId]);
    }

    header("Location: admin_panel.php?msg=Acțiune efectuată cu succes!");
    exit;
}

// Ia toți userii cu poze
$stmt = $db->query("SELECT id, username, gallery, gallery_status FROM users WHERE gallery IS NOT NULL AND gallery <> ''");
$usersWithPhotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Construiește array-ul cu DOAR pozele pending de validat
$pendingPhotos = [];
foreach ($usersWithPhotos as $user) {
    $photos = $user['gallery'] ? explode(',', $user['gallery']) : [];
    $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
    foreach ($photos as $idx => $photo) {
        if (($statuses[$idx] ?? null) === 'pending') {
            // Calea corectă
            $src = (strpos($photo, '/') === false ? 'uploads/' . $user['id'] . '/' . $photo : $photo);
            $pendingPhotos[] = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'photo' => $photo,
                'src' => $src
            ];
        }
    }
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
    <?php if (!empty($pendingPhotos)) : ?>
        <div class="admin-photo-validate-box">
            <button class="admin-nav-btn" id="adminPrevBtn">&#8592;</button>
            <div class="admin-photo-inner">
                <img src="<?= htmlspecialchars($pendingPhotos[0]['src']) ?>" class="admin-photo-img" id="adminPhotoPreview" />
                <form method="POST" id="adminValidateForm">
                    <input type="hidden" name="user_id" id="adminPhotoUserId" value="<?= $pendingPhotos[0]['user_id'] ?>">
                    <input type="hidden" name="photo_name" id="adminPhotoNameInput" value="<?= htmlspecialchars($pendingPhotos[0]['photo']) ?>">
                    <div class="admin-validate-actions">
                        <button type="submit" name="action" value="approve" class="admin-validate-btn">Aprobă</button>
                        <button type="submit" name="action" value="reject" class="admin-reject-btn">Respinge</button>
                    </div>
                </form>
                <div class="admin-photo-username"><i class="fa fa-user"></i> <span id="adminPhotoUsername"><?= htmlspecialchars($pendingPhotos[0]['username']) ?></span></div>
            </div>
            <button class="admin-nav-btn" id="adminNextBtn">&#8594;</button>
        </div>
        <script>
        const adminPhotos = <?= json_encode(array_column($pendingPhotos, 'src')); ?>;
        const adminUsers = <?= json_encode(array_column($pendingPhotos, 'user_id')); ?>;
        const adminPhotoNames = <?= json_encode(array_column($pendingPhotos, 'photo')); ?>;
        const adminUsernames = <?= json_encode(array_column($pendingPhotos, 'username')); ?>;
        let currentPhoto = 0;

        function updatePhotoDisplay() {
            document.getElementById('adminPhotoPreview').src = adminPhotos[currentPhoto];
            document.getElementById('adminPhotoUserId').value = adminUsers[currentPhoto];
            document.getElementById('adminPhotoNameInput').value = adminPhotoNames[currentPhoto];
            document.getElementById('adminPhotoUsername').textContent = adminUsernames[currentPhoto];
        }
        document.getElementById('adminPrevBtn').onclick = function() {
            if (adminPhotos.length === 0) return;
            currentPhoto = (currentPhoto - 1 + adminPhotos.length) % adminPhotos.length;
            updatePhotoDisplay();
        };
        document.getElementById('adminNextBtn').onclick = function() {
            if (adminPhotos.length === 0) return;
            currentPhoto = (currentPhoto + 1) % adminPhotos.length;
            updatePhotoDisplay();
        };
        </script>
    <?php else: ?>
        <div style='text-align:center; margin-top:32px;'>Nu există poze de validat în acest moment.</div>
    <?php endif; ?>
</div>
<div class="admin-navbar">
    <a class="admin-icon" href="index.php"><i class="fas fa-home"></i></a>
    <a class="admin-icon" href="matches.php"><i class="fas fa-heart"></i></a>
    <a class="admin-icon" href="messages.php"><i class="fas fa-comments"></i></a>
    <a class="admin-icon active" href="profile.php"><i class="fas fa-user"></i></a>
</div>
</body>
</html>
