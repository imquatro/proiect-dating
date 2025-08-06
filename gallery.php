<?php
session_start();
require_once __DIR__ . '/includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

// Handle photo actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    $stmt = $db->prepare('SELECT gallery, gallery_status FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $gallery = $user['gallery'] ? array_filter(explode(',', $user['gallery'])) : [];
    $statuses = $user['gallery_status'] ? explode(',', $user['gallery_status']) : [];
    $index = array_search($filename, $gallery);

    if ($index !== false) {
        if (isset($_POST['delete'])) {
            // Remove file from filesystem
            $filePath = 'uploads/' . $user_id . '/' . $filename;
            if (is_file($filePath)) {
                unlink($filePath);
            }
            // Remove from arrays
            array_splice($gallery, $index, 1);
            array_splice($statuses, $index, 1);
        } elseif (isset($_POST['set_profile'])) {
            // Move selected photo to first position
            $file = $gallery[$index];
            $status = $statuses[$index];
            array_splice($gallery, $index, 1);
            array_splice($statuses, $index, 1);
            array_unshift($gallery, $file);
            array_unshift($statuses, $status);
        }
        // Update database
        $stmt = $db->prepare('UPDATE users SET gallery = ?, gallery_status = ? WHERE id = ?');
        $stmt->execute([implode(',', $gallery), implode(',', $statuses), $user_id]);
    }
    header('Location: gallery.php');
    exit;
}

$stmt = $db->prepare('SELECT gallery, gallery_status FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$gallery = $user['gallery'] ? array_filter(explode(',', $user['gallery'])) : [];

ob_start();
?>
<div class="profile-container">
    <div class="profile-gallery">
        <?php if (!empty($gallery)): ?>
            <?php foreach ($gallery as $i => $photo): ?>
                <div class="slide <?= $i === 0 ? 'active' : '' ?>">
                    <img src="uploads/<?= $user_id ?>/<?= htmlspecialchars($photo) ?>" class="profile-img" alt="Photo">
                    <div class="gallery-btns">
                        <form method="POST">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($photo) ?>">
                            <button type="submit" name="set_profile" class="profile-upload-btn"><i class="fas fa-user-check"></i> Set as profile</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Delete this photo?');">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($photo) ?>">
                            <button type="submit" name="delete" class="profile-upload-btn"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
            <button class="gallery-arrow left" onclick="prevSlide()" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
            <button class="gallery-arrow right" onclick="nextSlide()" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
        <?php else: ?>
            <p style="text-align:center;">No photos in gallery.</p>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
$activePage = 'profile';
$pageCss = 'assets_css/gallery.css';
$extraJs = <<<'JS'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let current = 0;

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