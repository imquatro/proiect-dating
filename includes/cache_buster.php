<?php
function asset(string $path): string {
    if (preg_match('#^https?://#', $path)) {
        return $path;
    }
    $fullPath = __DIR__ . '/../' . ltrim($path, '/');
    if (is_file($fullPath)) {
        return $path . '?v=' . filemtime($fullPath);
    }
    return $path;
}