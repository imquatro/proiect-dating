<?php
$slotId = isset($_GET['slot']) ? intval($_GET['slot']) : 0;
$apply = isset($_GET['apply']);

if ($apply && $slotId) {
    $source = __DIR__ . '/../../img/default.png';
    $dest = __DIR__ . "/../../img/slot{$slotId}.png";
    if (file_exists($source)) {
        copy($source, $dest);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

$bgImagePath = '../../img/bg2.png';
$bgImage = $bgImagePath . '?v=' . filemtime(__DIR__ . '/../' . $bgImagePath);
?>
<div id="crop-slot" style="background: url('<?php echo $bgImage; ?>') no-repeat center/cover;">
    <h2>Crop Slot Placeholder</h2>
</div>