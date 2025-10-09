<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$isVip = !empty($user['vip']);

$gallery = !empty($user['gallery']) ? array_filter(explode(',', $user['gallery'])) : [];
$profile_photo = 'default-avatar.png';
if (!empty($gallery)) {
    $candidate = 'uploads/' . $user_id . '/' . $gallery[0];
    if (is_file($candidate)) {
        $profile_photo = $candidate;
    }
}

ob_start();
?>
<style>
/* Settings overlay - complet sub meniurile template-ului */
.settings-overlay {
    position: fixed;
    top: 60px;
    left: 0;
    width: 100%;
    height: calc(100vh - 120px);
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Meniurile template-ului au z-index mai mare */
.top-bar {
    z-index: 20 !important;
}

.bottom-nav {
    z-index: 20 !important;
}

.settings-panel {
    width: 90%;
    max-width: 600px;
    height: calc(100vh - 140px);
    max-height: calc(100vh - 140px);
    background: url('img/bg2.png') center/cover no-repeat !important;
    border-radius: 12px;
    box-shadow: 0 0 30px rgba(255, 255, 255, 0.6);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: url('img/bg2.png') center/cover no-repeat !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.settings-header {
    background: #ffe9a3;
    padding: 15px 20px;
    color: #4a3a00;
    border-bottom: 2px solid #f6cf49;
    border-radius: 12px 12px 0 0;
}

.settings-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.nav-btn {
    padding: 8px 16px;
    background: #ffe9a3;
    border: 1px solid #f6cf49;
    border-radius: 6px;
    color: #6c4e09;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    font-size: 14px;
    flex: 1;
    text-align: center;
    cursor: pointer;
}

.nav-btn:hover {
    background: #f6cf49;
    border-color: #e6b800;
}

.nav-btn.active {
    background: #e6b800;
    border-color: #d4a000;
    color: #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.settings-content::-webkit-scrollbar {
    display: none !important;
}

/* Design original pentru profile */
.profile-container {
    width: 100%;
    max-width: 620px;
    margin: 0 auto;
    background: transparent !important;
    border-radius: 0px;
    box-shadow: none;
    padding: 20px 12px 80px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 0;
}

.photo-frame {
    position: relative;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 15px;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info-list {
    width: 100%;
    max-width: 400px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
}

.info-label {
    font-weight: 500;
    color: #fff;
}

.info-value {
    color: #ffd700;
    font-weight: 600;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    color: #fff;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group input::placeholder, .form-group textarea::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.apply-frame-btn {
    background: linear-gradient(135deg, #ffd700, #ffb300);
    color: #333;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin: 10px 5px;
}

.apply-frame-btn:hover {
    background: linear-gradient(135deg, #ffb300, #ff8f00);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 179, 0, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .settings-panel {
        width: 95%;
        height: 95vh;
    }
    
    .settings-nav {
        gap: 6px;
    }
    
    .nav-btn {
        padding: 6px 12px;
        font-size: 12px;
    }
}
</style>

<div class="settings-overlay">
    <div class="settings-panel">
        <div class="settings-header">
            <h2 style="margin: 0; text-align: center;">Profile Settings</h2>
            
            <div class="settings-nav">
                <a href="settings_profile.php" class="nav-btn active">Profile</a>
                <a href="settings_bank.php" class="nav-btn">Bank</a>
                <a href="settings_helpers.php" class="nav-btn">Helpers</a>
                <?php
                $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $isAdmin = $stmt->fetchColumn() == 1;
                if ($isAdmin): ?>
                <a href="settings_admin.php" class="nav-btn">Admin Panel</a>
                <?php endif; ?>
                <a href="#" class="nav-btn" id="logoutBtn">Logout</a>
            </div>
        </div>

        <div class="settings-content">
            <div class="profile-container">
                <div class="photo-frame">
                    <img src="<?= htmlspecialchars($profile_photo) ?>" class="profile-img" alt="Profile photo">
                </div>
                
                <div class="profile-info-list">
                    <div class="info-item">
                        <span class="info-label">Username:</span>
                        <span class="info-value"><?= htmlspecialchars($user['username']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Level:</span>
                        <span class="info-value"><?= htmlspecialchars($user['level']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">VIP Status:</span>
                        <span class="info-value"><?= $isVip ? 'VIP Active' : 'Not VIP' ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="profileDescription" placeholder="Write your description..." rows="6" style="min-height: 120px; resize: vertical;"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                </div>

                <button class="apply-frame-btn" id="saveDescBtn">Save Description</button>

                <div class="profile-upload-card" style="margin-top: 15px;">
                    <form action="upload_photo.php" method="POST" enctype="multipart/form-data" id="upload-photo-form">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <button type="button" class="profile-upload-btn custom-add-btn" id="add-photos-btn" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-images"></i> Add Photos
                        </button>
                        <div id="file-input-container" style="display:none; margin-top: 10px;">
                            <input type="file" name="file" accept="image/*" required id="profile-photo-input" style="width: 100%; padding: 8px; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 6px; background: rgba(255, 255, 255, 0.1); color: #fff;">
                            <button type="submit" class="profile-upload-btn" id="upload-btn" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #ffd700, #ffb300); color: #333; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;">
                                <i class="fas fa-upload"></i> Upload Photo
                            </button>
                        </div>
                    </form>
                </div>

                <div class="profile-upload-card" style="margin-top: 10px;">
                    <a href="gallery.php" class="profile-upload-btn" style="display: block; text-align: center; padding: 12px; background: rgba(255, 255, 255, 0.2); color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;"><i class="fas fa-images"></i> View Gallery</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Add Photos button functionality
    const addPhotosBtn = document.getElementById('add-photos-btn');
    const fileInputContainer = document.getElementById('file-input-container');
    const profilePhotoInput = document.getElementById('profile-photo-input');
    
    if (addPhotosBtn && fileInputContainer) {
        addPhotosBtn.addEventListener('click', function() {
            addPhotosBtn.style.display = 'none';
            fileInputContainer.style.display = 'block';
        });
        
        // Click outside to cancel
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#upload-photo-form') && fileInputContainer.style.display === 'block') {
                fileInputContainer.style.display = 'none';
                addPhotosBtn.style.display = 'flex';
                profilePhotoInput.value = '';
            }
        });
    }
    
    // Save description
    const saveDescBtn = document.getElementById('saveDescBtn');
    const profileDescription = document.getElementById('profileDescription');
    
    if (saveDescBtn && profileDescription) {
        saveDescBtn.addEventListener('click', function() {
            const description = profileDescription.value;
            fetch('update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'save_desc=1&description=' + encodeURIComponent(description)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Description updated successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating description');
            });
        });
    }

    // Close overlay when clicking outside - revine la welcome.php
    const overlay = document.querySelector('.settings-overlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                window.location.href = 'welcome.php';
            }
        });
    }

    // Logout confirmation
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();

$pageTitle = 'Profile Settings';
$pageCss = '';

include 'template.php';
?>
