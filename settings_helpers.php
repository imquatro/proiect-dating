<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helper_images.php';

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/includes/update_last_active.php';

// Get all helpers
$helpers = $db->query('SELECT id,name,image,waters,feeds,harvests FROM helpers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($helpers as &$h) {
    $h['image'] = resolve_helper_image($h['image']);
    if (strpos($h['image'], 'img/') !== 0) {
        $h['image'] = 'img/' . ltrim($h['image'], '/');
    }
}
unset($h);

// Get user's current helper from user_helpers table
$stmt = $db->prepare('SELECT helper_id FROM user_helpers WHERE user_id = ?');
$stmt->execute([$user_id]);
$currentHelperId = $stmt->fetchColumn();

ob_start();
?>
<style>
/* Settings overlay - NU acoperă meniurile template-ului */
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
    z-index: 1000;
}

/* Asigură că meniurile template-ului rămân vizibile */
.top-bar {
    z-index: 1001 !important;
}

.bottom-nav {
    z-index: 1001 !important;
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

.settings-content {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: url('img/bg2.png') center/cover no-repeat !important;
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.settings-content::-webkit-scrollbar {
    display: none !important;
}

/* Design original pentru helpers - identic cu helperi.css */
#helpersList {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.helper-card {
    position: relative;
    width: calc(50% - 5px);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    background: rgba(0, 0, 0, 0.2);
    color: #fff;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.helper-card:hover {
    background: rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.helper-card.selected {
    outline: 2px solid #ffd700;
    background: rgba(255, 215, 0, 0.1);
}

.helper-card img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-bottom: 8px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.helper-card .helper-stats {
    font-size: 12px;
    text-align: center;
    margin-bottom: 8px;
    line-height: 1.4;
}

.helper-card .helper-stats span {
    display: block;
}

.helper-card .apply-helper-btn {
    background: linear-gradient(135deg, #ffd700, #ffb300);
    color: #333;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 12px;
}

.helper-card .apply-helper-btn:hover {
    background: linear-gradient(135deg, #ffb300, #ff8f00);
    transform: translateY(-1px);
}

.helper-card .apply-helper-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.current-helper {
    background: rgba(0, 0, 0, 0.4);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: #fff;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.no-helper {
    background: rgba(255, 0, 0, 0.2);
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    color: #fff;
    text-align: center;
    border: 1px solid rgba(255, 0, 0, 0.3);
}

.section-title {
    color: #fff;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
    text-align: center;
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
    
    .helper-card {
        width: 100%;
    }
}
</style>

<div class="settings-overlay">
    <div class="settings-panel">
        <div class="settings-header">
            <h2 style="margin: 0; text-align: center;">Helper Settings</h2>
            
            <div class="settings-nav">
                <a href="settings_profile.php" class="nav-btn">Profile</a>
                <a href="settings_bank.php" class="nav-btn">Bank</a>
                <a href="settings_helpers.php" class="nav-btn active">Helpers</a>
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
            <?php if ($currentHelperId): ?>
                <?php
                $currentHelper = null;
                foreach ($helpers as $helper) {
                    if ($helper['id'] == $currentHelperId) {
                        $currentHelper = $helper;
                        break;
                    }
                }
                ?>
                <?php if ($currentHelper): ?>
                <div class="current-helper">
                    <h3 style="margin-top: 0;">Current Helper</h3>
                    <img src="<?= htmlspecialchars($currentHelper['image']) ?>" alt="<?= htmlspecialchars($currentHelper['name']) ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-bottom: 10px;">
                    <div style="font-weight: bold; font-size: 18px;"><?= htmlspecialchars($currentHelper['name']) ?></div>
                    <div style="font-size: 14px; margin-top: 5px;">
                        Water: <?= $currentHelper['waters'] ?> | 
                        Feed: <?= $currentHelper['feeds'] ?> | 
                        Harvest: <?= $currentHelper['harvests'] ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-helper">
                    <h3 style="margin-top: 0;">No Helper Selected</h3>
                    <p>Choose a helper below to get farming assistance!</p>
                </div>
            <?php endif; ?>

            <div class="section-title">Available Helpers</div>
            <div id="helpersList">
                <?php foreach ($helpers as $helper): ?>
                <div class="helper-card <?= $helper['id'] == $currentHelperId ? 'selected' : '' ?>" data-helper-id="<?= $helper['id'] ?>">
                    <img src="<?= htmlspecialchars($helper['image']) ?>" alt="<?= htmlspecialchars($helper['name']) ?>">
                    <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;"><?= htmlspecialchars($helper['name']) ?></div>
                    <div class="helper-stats">
                        <span>Water: <?= $helper['waters'] ?></span>
                        <span>Feed: <?= $helper['feeds'] ?></span>
                        <span>Harvest: <?= $helper['harvests'] ?></span>
                    </div>
                    <button class="apply-helper-btn" data-helper-id="<?= $helper['id'] ?>">
                        <?= $helper['id'] == $currentHelperId ? 'Applied' : 'Apply' ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const helperCards = document.querySelectorAll('.helper-card');
    const applyButtons = document.querySelectorAll('.apply-helper-btn');

    // Helper selection and application
    applyButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const helperId = btn.getAttribute('data-helper-id');
            
            if (btn.disabled) return;
            
            // Here you would make an AJAX call to apply the helper
            fetch('apply_helper.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                credentials: 'same-origin',
                body: 'helper_id=' + helperId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    helperCards.forEach(card => card.classList.remove('selected'));
                    btn.closest('.helper-card').classList.add('selected');
                    
                    applyButtons.forEach(b => {
                        b.textContent = 'Apply';
                        b.disabled = false;
                    });
                    btn.textContent = 'Applied';
                    btn.disabled = true;
                    
                    alert('Helper applied successfully!');
                } else {
                    alert('Error applying helper: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error applying helper. Please try again.');
            });
        });
    });

    // Card click to select (visual feedback)
    helperCards.forEach(card => {
        card.addEventListener('click', () => {
            if (!card.classList.contains('selected')) {
                helperCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            }
        });
    });

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

$pageTitle = 'Helper Settings';
$pageCss = '';

include 'template.php';
?>
