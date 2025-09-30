<?php
$activePage = 'barn';
ob_start();
?>
<div class="barn-container">
    <div class="barn-panel">
        <div class="barn-settings-bar">
            <button id="barn-settings" class="barn-settings"><i class="fas fa-cog"></i></button>
        </div>
        <div id="barn-slots" class="barn-slots"></div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/barn.css';
$extraJs = '<script src="assets_js/barn.js"></script>';
include 'template.php';
?>