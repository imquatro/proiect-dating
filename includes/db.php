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

// Ensure helpers tables exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS helpers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        image VARCHAR(255) NOT NULL,
        message_file VARCHAR(255) NOT NULL,
        waters INT NOT NULL DEFAULT 0,
        feeds INT NOT NULL DEFAULT 0,
        harvests INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure daily action columns exist
    try {
        $db->exec("ALTER TABLE helpers ADD COLUMN IF NOT EXISTS waters INT NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE helpers ADD COLUMN IF NOT EXISTS feeds INT NOT NULL DEFAULT 0");
        $db->exec("ALTER TABLE helpers ADD COLUMN IF NOT EXISTS harvests INT NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
        // ignore if insufficient privileges or other errors
    }

    $db->exec("CREATE TABLE IF NOT EXISTS user_helpers (
        user_id INT NOT NULL PRIMARY KEY,
        helper_id INT NOT NULL,
        waters INT NOT NULL DEFAULT 0,
        feeds INT NOT NULL DEFAULT 0,
        harvests INT NOT NULL DEFAULT 0,
        last_action_date DATE DEFAULT CURDATE(),
        FOREIGN KEY (helper_id) REFERENCES helpers(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    try {
        $db->exec("ALTER TABLE user_helpers ADD COLUMN IF NOT EXISTS last_action_date DATE DEFAULT CURDATE()");
    } catch (PDOException $e) {
        // ignore if insufficient privileges or other errors
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}

// Ensure user_preferences table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        loading_style VARCHAR(20) DEFAULT 'variant-1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}