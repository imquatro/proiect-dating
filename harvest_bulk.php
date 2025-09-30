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
require_once __DIR__ . '/includes/level_helpers.php';
require_once __DIR__ . '/includes/achievement_helpers.php';

ensureBarnSchema($db);

$data = json_decode(file_get_contents('php://input'), true);
$slotId = isset($data['slot']) ? (int)$data['slot'] : 0;
if (!$slotId) {
    echo json_encode(['success' => false]);
    exit;
}
$userId = (int)$_SESSION['user_id'];
$vipStmt = $db->prepare('SELECT vip FROM users WHERE id = ?');
$vipStmt->execute([$userId]);
$isVip = (int)$vipStmt->fetchColumn() > 0;
$xpPerSlot = $isVip ? 8 : 2;

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

    $itemStmt = $db->prepare('SELECT production, image_product FROM farm_items WHERE id = ?');
    $itemStmt->execute([$itemId]);
    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $qtyPerSlot = (int)$item['production'];
    $img = $item['image_product'];
    if (strpos($img, 'img/') !== 0) {
        $img = 'img/' . ltrim($img, '/');
    }
    $maxPerSlot = ($qtyPerSlot === 1) ? 1 : 1000;

    $readyStmt = $db->prepare('SELECT up.slot_number
                               FROM user_plants up
                               JOIN farm_items f ON f.id = up.item_id
                               LEFT JOIN user_slot_states uss ON uss.user_id = up.user_id AND uss.slot_number = up.slot_number
                               WHERE up.user_id = ? AND up.item_id = ?
                                 AND IFNULL(uss.water_remaining, f.water_times) <= 0
                                 AND IFNULL(uss.feed_remaining, f.feed_times) <= 0
                               ORDER BY up.slot_number');
    $readyStmt->execute([$userId, $itemId]);
    $allSlots = $readyStmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$allSlots) {
        $db->rollBack();
        echo json_encode(['success' => false]);
        exit;
    }
    $readyCount = count($allSlots);

    $capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ?');
    $capStmt->execute([$userId]);
    $capacity = (int)$capStmt->fetchColumn();
    if (!$capacity) {
        $capacity = 4;
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

    $available = 0;
    foreach ($existingSlots as $es) {
        $available += max(0, $maxPerSlot - (int)$es['quantity']);
    }
    $freeSlots = $capacity - count($usedSlots);
    if ($freeSlots > 0) {
        $available += $freeSlots * $maxPerSlot;
    }

    $maxHarvestableSlots = min($readyCount, intdiv($available, max(1, $qtyPerSlot)));
    if ($maxHarvestableSlots <= 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'barn_full']);
        exit;
    }

    $slotsToHarvest = array_slice($allSlots, 0, $maxHarvestableSlots);
    $totalQty = $qtyPerSlot * $maxHarvestableSlots;

    $remaining = $totalQty;
    if ($qtyPerSlot > 1) {
        foreach ($existingSlots as $es) {
            if ($remaining <= 0) break;
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
    $tableCheck = $db->query("SHOW TABLES LIKE 'user_slot_states'");
    $delState = null;
    if ($tableCheck && $tableCheck->rowCount() > 0) {
        $delState = $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?');
    }

    $slotImages = [];
    foreach ($slotsToHarvest as $s) {
        $delPlant->execute([$userId, $s]);
        if ($delState) {
            $delState->execute([$userId, $s]);
        }
        $base = get_slot_image($s, $userId);
        $basePath = __DIR__ . '/' . $base;
        $slotImages[] = [
            'slot' => (int)$s,
            'image' => $base . '?v=' . (file_exists($basePath) ? filemtime($basePath) : time())
        ];
    }

    $xpResult = add_xp($db, $userId, $xpPerSlot * $maxHarvestableSlots);
    // Update total harvest count
    $db->prepare('UPDATE users SET harvests = harvests + ? WHERE id = ?')
       ->execute([$totalQty, $userId]);

    $db->commit();

    // Award achievements after harvest
    check_and_award_achievements($db, $userId);

    echo json_encode([
        'success' => true,
        'item' => ['item_id' => (int)$itemId, 'quantity' => $totalQty, 'image' => $img],
        'slots' => $slotImages,
        'levelUp' => $xpResult['levelUp'],
        'newLevel' => $xpResult['newLevel'],
        'money' => $xpResult['money'],
        'xpGain' => $xpResult['xpGain'],
        'xpPerSlot' => $maxHarvestableSlots > 0 ? ($xpResult['xpGain'] / $maxHarvestableSlots) : 0
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false]);
}