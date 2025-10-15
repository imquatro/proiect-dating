<?php
$activePage = 'diverse';
session_start();
ob_start();
?>
<div class="diverse-container"></div>
<?php
$content = ob_get_clean();
$pageTitle = 'Diverse';
$pageCss = 'assets_css/diverse.css';
$extraCss = [];
$extraJs = '';
include 'template.php';
?>