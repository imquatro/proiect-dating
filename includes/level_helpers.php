<?php
// Helper functions for XP and leveling system

/**
 * XP needed to go from the given level to the next level.
 * Early levels (1-69) are easier; from 70 onwards they require more XP.
 */
function xp_to_next_level(int $level): int {
    if ($level < 70) {
        return 100 + $level * 50; // gradually increasing
    }
    // make levels progressively harder after 70
    return 100 + 70 * 50 + ($level - 69) * 200;
}

/**
 * Total XP required to reach a specific level.
 */
function total_xp_for_level(int $level): int {
    $xp = 0;
    for ($i = 1; $i < $level; $i++) {
        $xp += xp_to_next_level($i);
    }
    return $xp;
}

/**
 * Determine level based on total XP.
 */
function level_from_xp(int $xp): int {
    $level = 1;
    while ($level < 120 && $xp >= total_xp_for_level($level + 1)) {
        $level++;
    }
    return $level;
}

/**
 * Add XP to a user and handle level-ups and rewards.
 * Returns an array with levelUp (bool), newLevel (int), reward (int), and money (int).
 */
function add_xp(PDO $db, int $userId, int $amount): array {
    $stmt = $db->prepare('SELECT xp, level, money FROM users WHERE id = ? FOR UPDATE');
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return ['levelUp' => false];
    }

    $xp = (int)($row['xp'] ?? 0);
    $level = (int)($row['level'] ?? 1);
    $money = (int)($row['money'] ?? 0);

    // Ensure XP is at least the minimum for the current level (for legacy users)
    $minXp = total_xp_for_level($level);
    if ($xp < $minXp) {
        $xp = $minXp;
    }

    $xp += $amount;
    $newLevel = level_from_xp($xp);
    $levelUp = $newLevel > $level;
    $reward = 0;
    if ($levelUp) {
        for ($lvl = $level + 1; $lvl <= $newLevel; $lvl++) {
            if ($lvl <= 10) {
                $reward += 100000 + ($lvl - 1) * 500;
            } elseif ($lvl % 10 === 0) {
                $reward += 1000000 + (($lvl / 10) - 1) * 5000000;
            }
        }
    }

    $moneyAfter = $money + $reward;
    $upd = $db->prepare('UPDATE users SET xp = ?, level = ?, money = ? WHERE id = ?');
    $upd->execute([$xp, $newLevel, $moneyAfter, $userId]);

    return [
        'levelUp' => $levelUp,
        'newLevel' => $newLevel,
        'reward' => $reward,
        'money' => $moneyAfter,
        'xpGain' => $amount,
        'xp' => $xp,
    ];
}
