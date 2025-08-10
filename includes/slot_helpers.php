<?php
require_once __DIR__ . '/db.php';

function get_slot_type($slotId, $userId)
{
    global $db;
    $stmt = $db->prepare('
        SELECT COALESCE(us.slot_type, ds.slot_type) AS slot_type
        FROM default_slots ds
        LEFT JOIN user_slots us ON us.user_id = ? AND us.slot_number = ds.slot_number
        WHERE ds.slot_number = ?
    ');
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

function get_slot_required_level($slotId)
{
    static $levels = null;
    if ($levels === null) {
        $levels = include __DIR__ . '/slot_levels.php';
       }
    return isset($levels[$slotId]) ? $levels[$slotId] : 0;
}
