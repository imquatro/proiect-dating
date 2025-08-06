<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
$profile_photo = 'default-avatar.png';
if (!empty($gallery)) {
    $candidate = 'uploads/' . $user_id . '/' . $gallery[0];
    if (is_file($candidate)) {
        $profile_photo = $candidate;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_desc'])) {
    $desc = trim($_POST['description']);
    $stmt = $db->prepare('UPDATE users SET description=? WHERE id=?');
    $stmt->execute([$desc, $user_id]);
    header('Location: profile.php?desc-updated=1');
    exit;
}

if (isset($_GET['error']) && $_GET['error'] === 'max_photos') {
    echo '<p style="color:red; font-weight:bold; text-align:center;">You have reached the maximum limit of 10 photos.</p>';
}

ob_start();
?>
<div class="profile-container">
    <div class="profile-gallery">
        <div class="photo-frame profile-photo-frame">
            <img src="<?= htmlspecialchars($profile_photo) ?>" class="profile-img" alt="Profile photo">
        </div>
    </div>
    <div class="profile-info-list">
        <div class="info-row"><span class="profile-label">Name:</span><span class="profile-value"><?=htmlspecialchars($user['username'])?></span></div>
        <div class="info-row"><span class="profile-label">Email:</span><span class="profile-value"><?=htmlspecialchars($user['email'])?></span></div>
        <div class="info-row"><span class="profile-label">Age:</span><span class="profile-value"><?=htmlspecialchars($user['age'])?></span></div>
        <div class="info-row"><span class="profile-label">Gender:</span><span class="profile-value"><?=htmlspecialchars($user['gender'])?></span></div>
        <div class="info-row"><span class="profile-label">Country:</span><span class="profile-value"><?=htmlspecialchars($user['country'])?></span></div>
        <div class="info-row"><span class="profile-label">City:</span><span class="profile-value"><?=htmlspecialchars($user['city'])?></span></div>
    </div>
    <div class="desc-edit-wrap">
        <div class="desc-title-row">
            <span style="font-weight:600;color:#7c4dff;">Description</span>
            <button type="button" class="desc-action-btn edit" onclick="toggleDescEdit()" id="descEditBtn">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
        <div id="desc-view-div" style="display:<?=!empty($user['description']) ? 'block':'none'?>;">
            <div class="desc-field"><?=!empty($user['description']) ? htmlspecialchars($user['description']) : '<span style="color:#aaa">No description</span>'?></div>
        </div>
        <form method="POST" style="margin:0;display:<?=empty($user['description']) ? 'block':'none'?>;" id="desc-edit-div">
            <textarea name="description" class="desc-field" maxlength="500"><?=htmlspecialchars($user['description'])?></textarea>
            <button type="submit" class="desc-action-btn" name="save_desc"><i class="fas fa-save"></i> Save</button>
        </form>
    </div>
    <div class="profile-upload-card">
        <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="upload-photo-form">
            <input type="hidden" name="user_id" value="<?=$user_id?>">
            <button type="button" class="profile-upload-btn" id="select-btn">
                <i class="fas fa-plus-circle"></i> Add photo
            </button>
            <input type="file" name="file" accept="image/*" required id="profile-photo-input" style="display:none;">
            <button type="submit" class="profile-upload-btn" id="upload-btn" style="display:none;">
                <i class="fas fa-upload"></i> Upload photo
            </button>
        </form>
    </div>
    <div class="profile-upload-card">
        <a href="gallery.php" class="profile-upload-btn"><i class="fas fa-images"></i> View gallery</a>
    </div>
</div>
<?php
$content = ob_get_clean();
$activePage = 'profile';
$pageCss = 'assets_css/profile.css';
$extraJs = <<<'JS'
<script>
function toggleDescEdit() {
    let editDiv = document.getElementById('desc-edit-div');
    let viewDiv = document.getElementById('desc-view-div');
    editDiv.style.display = (editDiv.style.display==='none'||editDiv.style.display==='') ? 'block':'none';
    viewDiv.style.display = (viewDiv.style.display==='none'||viewDiv.style.display==='') ? 'block':'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const selectBtn = document.getElementById('select-btn');
    const input = document.getElementById('profile-photo-input');
    const uploadBtn = document.getElementById('upload-btn');
    selectBtn.addEventListener('click', function() {
        input.click();
    });
    input.addEventListener('change', function() {
        uploadBtn.style.display = input.files.length > 0 ? 'inline-block' : 'none';
    });
});
</script>
JS;
$profilePhoto = $profile_photo;
include 'template.php';
?>