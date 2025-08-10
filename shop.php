<?php
$activePage = 'shop';
ob_start();
?>
<div class="shop-container">
    <div id="shopPanel" class="shop-panel">
        <div class="shop-tabs">
            <button class="tab-btn active" data-tab="animals">Animale</button>
            <button class="tab-btn" data-tab="plants">Plante</button>
            <button class="tab-btn" data-tab="trees">Copaci</button>
        </div>
        <div class="shop-tab-content">
            <div class="tab-content active" id="animals">Zona Animale</div>
            <div class="tab-content" id="plants">Zona Plante</div>
            <div class="tab-content" id="trees">Zona Copaci</div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'assets_css/shop.css';
$extraJs = '<script src="assets_js/shop.js"></script>';
include 'template.php';
?>