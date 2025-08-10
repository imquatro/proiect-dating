<?php
$activePage = 'diverse';
ob_start();
?>
<div class="diverse-container">
    <div id="diversePanel" class="diverse-panel">
        <div class="diverse-grid">
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
            <div class="diverse-item"></div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageTitle = 'Diverse';
$pageCss = 'assets_css/diverse.css';
include 'template.php';
?>