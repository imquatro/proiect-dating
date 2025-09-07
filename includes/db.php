<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/level_helpers.php';

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit('Eroare conexiune DB: ' . $e->getMessage());
}

// Ensure `level` column exists in `users` table
try {
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'level'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE users ADD COLUMN level INT(11) NOT NULL DEFAULT 1");
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}
// Ensure `xp` column exists in `users` table
try {
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'xp'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE users ADD COLUMN xp INT(11) NOT NULL DEFAULT 0");
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}

// Ensure `money` column has default 10000 and update existing users
try {
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'money'")->fetch(PDO::FETCH_ASSOC);
    if (!$col) {
        $db->exec("ALTER TABLE users ADD COLUMN money INT(11) NOT NULL DEFAULT 10000");
    } else {
        $default = isset($col['Default']) ? (int)$col['Default'] : null;
        if ($default !== 10000) {
            $db->exec("ALTER TABLE users MODIFY money INT(11) NOT NULL DEFAULT 10000");
        }
    }
    $db->exec("UPDATE users SET money = 10000 WHERE money IS NULL OR money < 10000");
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}

// Ensure `updated_at` column and useful index exist for `user_slot_states`
try {
    $col = $db->query("SHOW COLUMNS FROM user_slot_states LIKE 'updated_at'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE user_slot_states ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL");
    }
    $idx = $db->query("SHOW INDEX FROM user_slot_states WHERE Key_name = 'idx_user_slot'")->fetch();
    if (!$idx) {
        $db->exec("CREATE INDEX idx_user_slot ON user_slot_states (user_id, slot_number)");
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}
