<?php
// Utility functions for awarding achievements based on player stats.

/**
 * Check achievements for the given user and award any that meet the criteria.
 * Supports level, account age, XP, harvest count, sales count and item-based achievements.
 */
function check_and_award_achievements(PDO $db, int $userId): void
{
    // Fetch user info including counters for harvests and sales
    $stmt = $db->prepare('SELECT level, xp, created_at, harvests, sales FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return;
    }

    $level = (int)($user['level'] ?? 1);
    $xp = (int)($user['xp'] ?? 0);
    $harvests = (int)($user['harvests'] ?? 0);
    $sales = (int)($user['sales'] ?? 0);
    $createdAt = new DateTime($user['created_at'] ?? 'now');
    $now = new DateTime();
    // Calculate account age in days to allow precise year-based achievements
    $accountAgeDays = (int)$createdAt->diff($now)->days;

    // Achievements already owned by the user
    $ownedStmt = $db->prepare('SELECT achievement_id FROM user_achievements WHERE user_id = ?');
    $ownedStmt->execute([$userId]);
    $owned = $ownedStmt->fetchAll(PDO::FETCH_COLUMN);
    $owned = array_flip($owned);

    // Fetch all achievements along with their requirements
    $achStmt = $db->query('SELECT id, level, years, harvest, sales, xp, item_id FROM achievements');
    $achievements = $achStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($achievements as $ach) {
        $id = (int)$ach['id'];
        if (isset($owned[$id])) {
            continue; // already has it
        }

        $reqLevel = (int)($ach['level'] ?? 0);
        $reqYears = (int)($ach['years'] ?? 0);
        $reqHarvest = (int)($ach['harvest'] ?? 0);
        $reqSales = (int)($ach['sales'] ?? 0);
        $reqXp = (int)($ach['xp'] ?? 0);
        $reqItem = $ach['item_id'] ?? null;

        // Skip achievements with no defined requirements
        if ($reqLevel === 0 && $reqYears === 0 && $reqHarvest === 0 &&
            $reqSales === 0 && $reqXp === 0 && !$reqItem) {
            continue;
        }

        // Convert required years to days for comparison
        $requiredDays = $reqYears > 0 ? $reqYears * 365 : 0;
        $hasItem = true;
        if ($reqItem) {
            $itemStmt = $db->prepare('SELECT 1 FROM user_barn WHERE user_id = ? AND item_id = ? LIMIT 1');
            $itemStmt->execute([$userId, $reqItem]);
            $hasItem = (bool)$itemStmt->fetchColumn();
        }

        if (($reqLevel > 0 && $level < $reqLevel) ||
            ($requiredDays > 0 && $accountAgeDays < $requiredDays) ||
            ($reqXp > 0 && $xp < $reqXp) ||
            ($reqHarvest > 0 && $harvests < $reqHarvest) ||
            ($reqSales > 0 && $sales < $reqSales) ||
            ($reqItem && !$hasItem)) {
            continue;
        }

        $ins = $db->prepare('INSERT INTO user_achievements (user_id, achievement_id, selected) VALUES (?, ?, 0)');
        $ins->execute([$userId, $id]);
    }
}