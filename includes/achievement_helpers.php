<?php
// Utility functions for awarding achievements based on player stats.

/**
 * Check achievements for the given user and award any that meet the criteria.
 * Currently supports level based achievements and account age (years).
 */
function check_and_award_achievements(PDO $db, int $userId): void
{
    // Fetch user info: level and registration date
    $stmt = $db->prepare('SELECT level, created_at FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        return;
    }

    $level = (int)($user['level'] ?? 1);
    $createdAt = new DateTime($user['created_at'] ?? 'now');
    $now = new DateTime();
    $years = (int)$createdAt->diff($now)->y;

    // Achievements already owned by the user
    $ownedStmt = $db->prepare('SELECT achievement_id FROM user_achievements WHERE user_id = ?');
    $ownedStmt->execute([$userId]);
    $owned = $ownedStmt->fetchAll(PDO::FETCH_COLUMN);
    $owned = array_flip($owned);

    // Fetch all achievements with level or years requirement
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

        // Only award achievements that rely solely on level/years
        if ($reqHarvest > 0 || $reqSales > 0 || $reqXp > 0 || $reqItem) {
            continue;
        }

        // Skip achievements with no defined requirements
        if ($reqLevel === 0 && $reqYears === 0) {
            continue;
        }

        if (($reqLevel > 0 && $level < $reqLevel) ||
            ($reqYears > 0 && $years < $reqYears)) {
            continue;
        }

        $ins = $db->prepare('INSERT INTO user_achievements (user_id, achievement_id, selected) VALUES (?, ?, 0)');
        $ins->execute([$userId, $id]);
    }
}