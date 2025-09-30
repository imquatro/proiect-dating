<?php
session_start();
$achFile = __DIR__ . '/includes/achievements.json';
$achievements = [];
if (is_file($achFile)) {
    $data = json_decode(file_get_contents($achFile), true);
    if (is_array($data)) {
        $achievements = $data;
    }
}
$mid = (int)ceil(count($achievements) / 2);
$left = array_slice($achievements, 0, $mid);
$right = array_slice($achievements, $mid);
?>
<div id="achievementsOverlay" class="achievements-overlay">
    <div class="achievements-panel">
        <h2>Achievements</h2>
        <div class="achievements-content">
            <div class="achievements-column">
                <?php foreach ($left as $a): ?>
                <div class="achievement-item"><?= htmlspecialchars($a['name']) ?></div>
                <?php endforeach; ?>
            </div>
            <div class="achievements-column">
                <?php foreach ($right as $a): ?>
                <div class="achievement-item"><?= htmlspecialchars($a['name']) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="achievements-close">Close</button>
    </div>
</div>