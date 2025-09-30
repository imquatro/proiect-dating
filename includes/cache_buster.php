<?php
function asset(string $path): string {
    static $version = null;
    if ($version === null) {
        $file = __DIR__ . '/../version.txt';
        $version = is_file($file) ? trim(file_get_contents($file)) : time();
    }
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    $sep = strpos($path, '?') !== false ? '&' : '?';
    return $path . $sep . 'v=' . $version;
}