<?php
function get_slot_image($slotId) {
    $file = __DIR__ . "/../img/slot{$slotId}.png";
    if (file_exists($file)) {
        return "img/slot{$slotId}.png";
    }
    return 'img/default.png';
}