<?php
$activePage = 'welcome';
ob_start();
?>
<div id="cs-slot-panel">
    <img src="img/default.png" alt="Current slot" id="cs-slot-image">
    <div id="cs-slot-actions">
        <button class="cs-slot-btn" id="cs-slot-shop"><i class="fas fa-store"></i><span>SHOP</span></button>
        <button class="cs-slot-btn" id="cs-slot-change"><i class="fas fa-random"></i><span>Change Plot Type</span></button>
        <button class="cs-slot-btn" id="cs-slot-swap"><i class="fas fa-exchange-alt"></i><span>Swap Plots</span></button>
    </div>
</div>
<?php
$content = ob_get_clean();
$pageCss = 'changeslots/slot-panel.css';
$extraJs = '<script src="changeslots/slot-panel.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>