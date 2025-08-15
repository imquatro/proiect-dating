<?php
$activePage = 'barn';
$pageCss = 'assets_css/barn.css';
$extraJs = '<script src="assets_js/barn.js"></script>';
$content = <<<HTML
<div class="barn-container">
    <div class="barn-panel">
        <div class="barn-header">
            <button id="barn-settings" class="barn-settings"><i class="fas fa-cog"></i></button>
        </div>
        <div id="barn-slots" class="barn-slots"></div>
    </div>
</div>
HTML;
include 'template.php';