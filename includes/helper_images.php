<?php
function resolve_helper_image(string $base): string {
    $base = basename($base);
    $dir = __DIR__ . '/../img/';
    $extensions = ['png', 'gif', 'jpg', 'jpeg'];
    foreach ($extensions as $ext) {
        $path = $dir . $base . '.' . $ext;
        if (is_file($path)) {
            return 'img/' . $base . '.' . $ext;
        }
    }
    return 'img/' . $base . '.png';
}