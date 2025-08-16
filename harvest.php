<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/slot_helpers.php';
require_once __DIR__ . '/includes/barn_helpers.php';

ensureBarnSchema($db);

$data = json_decode(file_get_contents('php://input'), true);
$slotId = isset($data['slot']) ? (int)$data['slot'] : 0;
if (!$slotId) {
    echo json_encode(['success' => false]);
    exit;
}
$userId = (int)$_SESSION['user_id'];

try {
    $db->beginTransaction();
    $stmt = $db->prepare('SELECT item_id FROM user_plants WHERE user_id = ? AND slot_number = ?');
    $stmt->execute([$userId, $slotId]);
    $itemId = $stmt->fetchColumn();
    if (!$itemId) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $stmt = $db->prepare('SELECT production, image_product FROM farm_items WHERE id = ?');
    $stmt->execute([$itemId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $qty = (int)$item['production'];
    $img = $item['image_product'];
    if (strpos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
    }

    $maxPerSlot = ($qty === 1) ? 1 : 1000;

    $capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ?');
    $capStmt->execute([$userId]);
    $capacity = (int)$capStmt->fetchColumn();
    if (!$capacity) {
        $capacity = 16;
        $db->prepare('INSERT INTO user_barn_info (user_id, capacity) VALUES (?, ?)')->execute([$userId, $capacity]);
    }

    $slotStmt = $db->prepare('SELECT slot_number, item_id, quantity FROM user_barn WHERE user_id = ? ORDER BY slot_number');
    $slotStmt->execute([$userId]);
    $rows = $slotStmt->fetchAll(PDO::FETCH_ASSOC);

    $usedSlots = [];
    $existingSlots = [];
    foreach ($rows as $r) {
        $usedSlots[] = (int)$r['slot_number'];
        if ((int)$r['item_id'] === (int)$itemId) {
            $existingSlots[] = $r;
        }
    }

    $remaining = $qty;
    if ($qty > 1) {
        foreach ($existingSlots as $es) {
            if ($remaining <= 0) {
                break;
            }
            $avail = $maxPerSlot - (int)$es['quantity'];
            if ($avail > 0) {
                $add = min($avail, $remaining);
                $db->prepare('UPDATE user_barn SET quantity = quantity + ? WHERE user_id = ? AND slot_number = ?')
                   ->execute([$add, $userId, (int)$es['slot_number']]);
                $remaining -= $add;
            }
        }
    }
    $usedSet = array_flip($usedSlots);
    $nextSlot = 1;
    while ($remaining > 0 && count($usedSet) < $capacity) {
        while (isset($usedSet[$nextSlot]) && $nextSlot <= $capacity) {
            $nextSlot++;
        }
        if ($nextSlot > $capacity) {
            break;
        }
        $add = min($maxPerSlot, $remaining);
        $db->prepare('INSERT INTO user_barn (user_id, slot_number, item_id, quantity) VALUES (?, ?, ?, ?)')
           ->execute([$userId, $nextSlot, $itemId, $add]);
        $usedSet[$nextSlot] = true;
        $remaining -= $add;
        $nextSlot++;
    }

    $delPlant = $db->prepare('DELETE FROM user_plants WHERE user_id = ? AND slot_number = ?');
    $delPlant->execute([$userId, $slotId]);

    $tableCheck = $db->query("SHOW TABLES LIKE 'user_slot_states'");
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        $delState = $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
        $delState->execute([$userId, $slotId]);
    }

    $base = get_slot_image($slotId, $userId);
    $basePath = __DIR__ . '/' . $base;
    $baseImage = $base . '?v=' . (file_exists($basePath) ? filemtime($basePath) : time());

    $db->commit();
    echo json_encode([
        'success' => true,
        'item' => ['item_id' => (int)$itemId, 'quantity' => $qty, 'image' => $img],
        'slotImage' => $baseImage
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}