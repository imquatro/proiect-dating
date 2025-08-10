<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$images = [];
foreach (glob(__DIR__ . '/img/*.{png,jpg,jpeg,gif}', GLOB_BRACE) as $img) {
    $images[] = 'img/' . basename($img);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Se încarcă...</title>
    <link rel="stylesheet" href="assets_css/index.css" />
    <link rel="stylesheet" href="assets_css/loading.css" />
    <link href="https://fonts.googleapis.com/css2?family=Luckiest+Guy&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-frame">
        <div class="text-container">
            <div id="message" class="typing"></div>
        </div>
        <div id="progress-container">
            <div id="progress-bar"></div>
            <div id="progress-text">0%</div>
        </div>
    </div>
    <script id="image-data" type="application/json"><?= json_encode($images) ?></script>
    <script src="assets_js/loading.js"></script>
</body>
</html>