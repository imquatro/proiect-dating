<?php
$activePage = 'settings';

ob_start();
?>
<button id="open-settings-panel" class="settings-open-btn">Setări</button>

<div id="settings-panel-overlay">
    <div id="settings-panel">
        <h2>Setările jocului</h2>
        <p>Ajustează opțiunile jocului după preferințe.</p>
        <?php for ($i = 1; $i <= 20; $i++): ?>
            <p>Opțiune de setare <?php echo $i; ?></p>
        <?php endfor; ?>
    </div>
</div>
<?php
$content = ob_get_clean();

$pageCss = 'assets_css/settings.css';
$extraJs = '<script src="assets_js/settings.js"></script>';

include 'template.php';
