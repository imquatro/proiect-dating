<?php
$activePage = 'welcome';
$bgImagePath = 'img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
$ajax = isset($_GET['ajax']);
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;

$slotTypes = [
    ['id' => 'default', 'name' => 'Default Plot', 'image' => 'img/default.png'],
    ['id' => 'crop', 'name' => 'Crop Plot', 'image' => 'img/default.png'],
];
ob_start();
?>
<div id="st-slotstype-panel" data-slot="<?php echo $slotId; ?>" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <div id="st-slotstype-list">
        <?php foreach ($slotTypes as $type): ?>
            <div class="st-slotstype-item" data-type="<?php echo htmlspecialchars($type['id']); ?>">
                <img src="<?php echo $type['image']; ?>" alt="<?php echo htmlspecialchars($type['name']); ?>">
                <div class="st-slotstype-name"><?php echo htmlspecialchars($type['name']); ?></div>
                <button class="st-slot-apply">Apply</button>
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