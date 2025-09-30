<?php
// Mapping of slot IDs to the player level required to unlock them.
// Each subsequent locked slot requires 5 more levels, starting at level 5 for slot 4.
// Slots unlocked by default and the last five premium slots are excluded.

$lockedSlots = array_merge([4, 5], range(9, 30));
$levels = [];
$level = 5;
foreach ($lockedSlots as $slot) {
    $levels[$slot] = $level;
    $level += 5;
}

return $levels;
