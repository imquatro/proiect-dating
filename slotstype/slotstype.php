<?php
$activePage = 'welcome';
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);

$slotTypes = [
    ['id' => 1, 'name' => 'Default Plot', 'image' => 'img/default.png'],
];

ob_start();
?>
<div id="st-slotstype-panel" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="st-slotstype-list">
        <?php foreach ($slotTypes as $type): ?>
            <div class="st-slotstype-item">
                <img src="<?php echo $type['image']; ?>" alt="<?php echo htmlspecialchars($type['name']); ?>">
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
if ($ajax) {
    echo $content;
    exit;
}
$pageCss = 'slotstype/slotstype.css';
$extraJs = '<script src="slotstype/slotstype.js"></script>';
$noScroll = true;
chdir('..');
include 'template.php';
?>