<?php
$activePage = 'diverse';
ob_start();
?>
<div class="diverse-container">
    <div id="diversePanel" class="diverse-panel">
        <div class="diverse-grid">
            <div class="diverse-item"><button type="button"><img src="img/admin.png" alt="Admin Panel"></button></div>
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
include 'template.php';
?>