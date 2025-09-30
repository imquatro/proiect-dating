<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helper_images.php';

function helper_user_is_online(PDO $db, int $userId, int $thresholdSeconds = 300): bool {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $sessionUserId = $_SESSION['user_id'] ?? null;
        if ($sessionUserId !== null && (int)$sessionUserId === $userId) {
            return true;
        }
    }
    $stmt = $db->prepare('SELECT last_active FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $lastActive = $stmt->fetchColumn();
    if (!$lastActive) {
        return false;
    }
    $lastActiveTs = strtotime($lastActive);
    if ($lastActiveTs === false) {
        return false;
    }
    return ($lastActiveTs >= time() - max(0, $thresholdSeconds));
}

function auto_harvest(PDO $db, int $userId, int $slotId): bool {
    try {
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT item_id FROM user_plants WHERE user_id = ? AND slot_number = ?');
        $stmt->execute([$userId, $slotId]);
        $itemId = $stmt->fetchColumn();
        if (!$itemId) {
            $db->rollBack();
            return false;
        }

        $stmt = $db->prepare('SELECT production FROM farm_items WHERE id = ?');
        $stmt->execute([$itemId]);
        $production = (int)$stmt->fetchColumn();
        if (!$production) {
            $db->rollBack();
            return false;
        }

        $capStmt = $db->prepare('SELECT capacity FROM user_barn_info WHERE user_id = ?');
        $capStmt->execute([$userId]);
        $capacity = (int)$capStmt->fetchColumn();
        if (!$capacity) {
            $capacity = 4;
            $db->prepare('INSERT INTO user_barn_info (user_id, capacity) VALUES (?, ?)')
               ->execute([$userId, $capacity]);
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
        $maxPerSlot = ($production === 1) ? 1 : 1000;
        $available = 0;
        foreach ($existingSlots as $es) {
            $available += max(0, $maxPerSlot - (int)$es['quantity']);
        }
        $freeSlots = $capacity - count($usedSlots);
        if ($freeSlots > 0) {
            $available += $freeSlots * $maxPerSlot;
        }
        if ($available < $production) {
            $db->rollBack();
            return false;
        }

        $remaining = $production;
        if ($production > 1) {
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

        $db->prepare('DELETE FROM user_plants WHERE user_id = ? AND slot_number = ?')
           ->execute([$userId, $slotId]);
        $db->prepare('DELETE FROM user_slot_states WHERE user_id = ? AND slot_number = ?')
           ->execute([$userId, $slotId]);
        $db->prepare('UPDATE users SET harvests = harvests + ? WHERE id = ?')
           ->execute([$production, $userId]);
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

function process_helper_actions(int $userId, bool $forceProcess = false): array {
    global $db;
    $summary = [
        'waterUsed' => 0,
        'waterLimit' => 0,
        'feedUsed' => 0,
        'feedLimit' => 0,
        'harvestUsed' => 0,
        'harvestLimit' => 0,
        'helper' => null
    ];

    $stmt = $db->prepare('SELECT uh.helper_id, uh.waters, uh.feeds, uh.harvests, uh.last_action_date, h.name, h.image, h.waters AS max_waters, h.feeds AS max_feeds, h.harvests AS max_harvests FROM user_helpers uh JOIN helpers h ON h.id = uh.helper_id WHERE uh.user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return $summary;
    }

    $summary['helper'] = [
        'id' => (int)$row['helper_id'],
        'name' => $row['name'],
        'image' => resolve_helper_image($row['image'])
    ];
    if (strpos($summary['helper']['image'], 'img/') !== 0) {
        $summary['helper']['image'] = 'img/' . ltrim($summary['helper']['image'], '/');
    }

    $today = date('Y-m-d');
    if ($row['last_action_date'] !== $today) {
        $db->prepare('UPDATE user_helpers SET waters = 0, feeds = 0, harvests = 0, last_action_date = ? WHERE user_id = ?')
           ->execute([$today, $userId]);
        $row['waters'] = $row['feeds'] = $row['harvests'] = 0;
    }

    $shouldProcess = $forceProcess ? true : !helper_user_is_online($db, $userId);

    // Watering
    $wStmt = $db->prepare('SELECT slot_number, water_interval FROM user_slot_states WHERE user_id = ? AND water_remaining > 0 AND (timer_end IS NULL OR timer_end <= NOW())');
    $wStmt->execute([$userId]);
    $wSlots = $wStmt->fetchAll(PDO::FETCH_ASSOC);
    $maxWaters = (int)$row['max_waters'];
    $usedWaters = (int)$row['waters'];
    $wLimit = min(count($wSlots), max(0, $maxWaters - $usedWaters));
    if ($shouldProcess && $wLimit > 0) {
        $upd = $db->prepare('UPDATE user_slot_states SET water_remaining = GREATEST(water_remaining-1,0), timer_type = "water", timer_end = DATE_ADD(NOW(), INTERVAL ? SECOND), updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
        foreach (array_slice($wSlots, 0, $wLimit) as $slot) {
            $interval = isset($slot['water_interval']) ? (int)$slot['water_interval'] : 0;
            $upd->execute([$interval, $userId, (int)$slot['slot_number']]);
            $usedWaters++;
        }
        $db->prepare('UPDATE user_helpers SET waters = ? WHERE user_id = ?')
           ->execute([$usedWaters, $userId]);
    }
    $summary['waterUsed'] = $usedWaters;
    $summary['waterLimit'] = $maxWaters;
    $wCountStmt = $db->prepare('SELECT COUNT(*) FROM user_slot_states WHERE user_id = ? AND water_remaining > 0 AND (timer_end IS NULL OR timer_end <= NOW())');
    $wCountStmt->execute([$userId]);
    $summary['needWater'] = (int)$wCountStmt->fetchColumn();

    // Feeding
    $fStmt = $db->prepare('SELECT slot_number, feed_interval FROM user_slot_states WHERE user_id = ? AND feed_remaining > 0 AND (timer_end IS NULL OR timer_end <= NOW())');
    $fStmt->execute([$userId]);
    $fSlots = $fStmt->fetchAll(PDO::FETCH_ASSOC);
    $maxFeeds = (int)$row['max_feeds'];
    $usedFeeds = (int)$row['feeds'];
    $fLimit = min(count($fSlots), max(0, $maxFeeds - $usedFeeds));
    if ($shouldProcess && $fLimit > 0) {
        $updF = $db->prepare('UPDATE user_slot_states SET feed_remaining = GREATEST(feed_remaining-1,0), timer_type = "feed", timer_end = DATE_ADD(NOW(), INTERVAL ? SECOND), updated_at = NOW() WHERE user_id = ? AND slot_number = ?');
        foreach (array_slice($fSlots, 0, $fLimit) as $slot) {
            $interval = isset($slot['feed_interval']) ? (int)$slot['feed_interval'] : 0;
            $updF->execute([$interval, $userId, (int)$slot['slot_number']]);
            $usedFeeds++;
        }
        $db->prepare('UPDATE user_helpers SET feeds = ? WHERE user_id = ?')
           ->execute([$usedFeeds, $userId]);
    }
    $summary['feedUsed'] = $usedFeeds;
    $summary['feedLimit'] = $maxFeeds;
    $fCountStmt = $db->prepare('SELECT COUNT(*) FROM user_slot_states WHERE user_id = ? AND feed_remaining > 0 AND (timer_end IS NULL OR timer_end <= NOW())');
    $fCountStmt->execute([$userId]);
    $summary['needFeed'] = (int)$fCountStmt->fetchColumn();

    // Harvesting
    $hStmt = $db->prepare('SELECT slot_number FROM user_slot_states WHERE user_id = ? AND timer_type = "harvest" AND (timer_end IS NULL OR timer_end <= NOW())');
    $hStmt->execute([$userId]);
    $hSlots = $hStmt->fetchAll(PDO::FETCH_COLUMN);
    $maxHarvests = (int)$row['max_harvests'];
    $usedHarvests = (int)$row['harvests'];
    $hLimit = min(count($hSlots), max(0, $maxHarvests - $usedHarvests));
    if ($shouldProcess && $hLimit > 0) {
        foreach (array_slice($hSlots, 0, $hLimit) as $slot) {
            if (auto_harvest($db, $userId, (int)$slot)) {
                $usedHarvests++;
            }
        }
        $db->prepare('UPDATE user_helpers SET harvests = ? WHERE user_id = ?')
           ->execute([$usedHarvests, $userId]);
    }
    $summary['harvestUsed'] = $usedHarvests;
    $summary['harvestLimit'] = $maxHarvests;
    $hCountStmt = $db->prepare('SELECT COUNT(*) FROM user_slot_states WHERE user_id = ? AND timer_type = "harvest" AND (timer_end IS NULL OR timer_end <= NOW())');
    $hCountStmt->execute([$userId]);
    $summary['needHarvest'] = (int)$hCountStmt->fetchColumn();

    return $summary;
}