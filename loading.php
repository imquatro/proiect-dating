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
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loading...</title>
    <link rel="stylesheet" href="assets_css/loading.css" />
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-frame">
        <div class="loading-container">
            <!-- 3D Loading Spinner -->
            <div class="loading-spinner">
                <div class="spinner-3d">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                </div>
            </div>
            
            <!-- Loading Message -->
            <div class="text-container">
                <div id="message" class="typing">Loading...</div>
            </div>
            
            <!-- Progress Bar -->
            <div id="progress-container">
                <div id="progress-text">0%</div>
                <div id="progress-wrap">
                    <div id="progress-bar"></div>
                    <div id="progress-tip"></div>
                </div>
            </div>
        </div>
    </div>
    <script id="image-data" type="application/json"><?= json_encode($images) ?></script>
    <script src="assets_js/loading.js"></script>
</body>
</html>