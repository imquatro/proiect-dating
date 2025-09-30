<?php
$activePage = 'diverse';
session_start();
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/db.php';
    $stmt = $db->prepare('SELECT is_admin FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->fetchColumn() == 1;
}
ob_start();
?>
<div class="diverse-container">
        <div class="diverse-grid">
            <?php if ($isAdmin): ?>
            <div class="diverse-item"><button type="button" id="open-admin-panel"><img src="img/admin.png" alt="Admin Panel"></button></div>
            <?php endif; ?>
            <div class="diverse-item"><button type="button"><img src="img/banca.png" alt="Banca"></button></div>
            <div class="diverse-item"><button type="button"><img src="img/lideri.png" alt="Lideri"></button></div>
            <div class="diverse-item"><button type="button"><img src="img/setari.png" alt="Setari"></button></div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Diverse';
$pageCss = 'assets_css/diverse.css';
$extraCss = $isAdmin ? ['farm_admin/admin-panel.css'] : [];
$extraJs = $isAdmin ? '<script src="farm_admin/admin-panel.js"></script>' : '';
include 'template.php';
?>