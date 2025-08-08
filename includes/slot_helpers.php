<?php
require_once __DIR__ . '/db.php';

function get_slot_type($slotId, $userId)
{
    global $db;
    $stmt = $db->prepare('SELECT slot_type FROM user_slots WHERE user_id = ? AND slot_number = ?');
    $stmt->execute([$userId, $slotId]);
    $type = $stmt->fetchColumn();
    return $type ?: 'crop';
}

function slot_image_from_type($type)
{
    switch ($type) {
        case 'tarc':
            return 'img/tarc1.png';
        case 'pool':
            return 'img/pool.png';
        default:
            return 'img/default.png';
    }
}

function get_slot_image($slotId, $userId = null)
{
    if (!$userId) {
        return 'img/default.png';
    }
    $type = get_slot_type($slotId, $userId);
    return slot_image_from_type($type);
}