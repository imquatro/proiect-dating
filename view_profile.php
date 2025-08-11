<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/update_last_active.php';

$profile_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($profile_id <= 0) {
    header('Location: friends.php');
    exit;
}

$stmt = $db->prepare('SELECT username, gender, city, created_at, gallery FROM users WHERE id = ?');
$stmt->execute([$profile_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$profile) {
    header('Location: friends.php');
    exit;
}

$gallery = !empty($profile['gallery']) ? array_filter(explode(',', $profile['gallery'])) : [];
$profile_photo = 'default-avatar.png';
if (!empty($gallery)) {
    $candidate = 'uploads/' . $profile_id . '/' . trim($gallery[0]);
    if (is_file($candidate)) {
        $profile_photo = $candidate;
    }
}

ob_start();
?>
<div class="profile-container">
    <div class="profile-gallery">
        <?php if (!empty($gallery)): ?>
            <?php foreach ($gallery as $i => $photo): ?>
                <div class="slide <?= $i === 0 ? 'active' : '' ?>">
                    <div class="photo-frame gallery-photo-frame">
                        <img src="uploads/<?= $profile_id ?>/<?= htmlspecialchars(trim($photo)) ?>" class="profile-img" alt="Photo">
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (count($gallery) > 1): ?>
                <button class="gallery-arrow left" onclick="prevSlide()" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                <button class="gallery-arrow right" onclick="nextSlide()" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        <?php else: ?>
            <div class="photo-frame gallery-photo-frame">
                <img src="<?= htmlspecialchars($profile_photo) ?>" class="profile-img" alt="Profile photo">
            </div>
        <?php endif; ?>
    </div>
    <div class="profile-info-list">
        <div class="info-row"><span class="profile-label">Name:</span><span class="profile-value"><?= htmlspecialchars($profile['username']) ?></span></div>
        <div class="info-row"><span class="profile-label">Gender:</span><span class="profile-value"><?= htmlspecialchars($profile['gender']) ?></span></div>
        <div class="info-row"><span class="profile-label">City:</span><span class="profile-value"><?= htmlspecialchars($profile['city']) ?></span></div>
        <div class="info-row"><span class="profile-label">Registered:</span><span class="profile-value"><?= htmlspecialchars(date('Y-m-d', strtotime($profile['created_at']))) ?></span></div>
    </div>
</div>
<?php
$content = ob_get_clean();
$activePage = '';
$pageCss = 'assets_css/gallery.css';
$extraJs = <<<'JS'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;
    let current = 0;
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    function update() {
        slides.forEach((s, i) => s.classList.toggle('active', i === current));
        if (prevBtn) prevBtn.disabled = current === 0;
        if (nextBtn) nextBtn.disabled = current === slides.length - 1;
    }

    window.nextSlide = function() {
        if (current < slides.length - 1) {
            current++;
            update();
        }
    };
    window.prevSlide = function() {
        if (current > 0) {
            current--;
            update();
        }
    };

    update();
});
</script>
JS;
include 'template.php';
?>