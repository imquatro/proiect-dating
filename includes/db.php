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
    
    // Sync MySQL timezone with PHP timezone for PvP time calculations
    $offset = date('P');
    $db->exec("SET time_zone = '{$offset}'");
} catch (PDOException $e) {
    exit('Database connection error: ' . $e->getMessage());
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

// Ensure dbd_screenshots table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS dbd_screenshots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        imgbb_url TEXT NOT NULL,
        imgbb_display_url TEXT NOT NULL,
        imgbb_delete_url TEXT,
        file_size INT,
        file_created_date DATETIME,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed BOOLEAN DEFAULT TRUE,
        UNIQUE KEY unique_filename (filename),
        INDEX idx_upload_date (upload_date),
        INDEX idx_file_created_date (file_created_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Add file_created_date column if it doesn't exist (for existing tables)
    $db->exec("ALTER TABLE dbd_screenshots ADD COLUMN IF NOT EXISTS file_created_date DATETIME AFTER file_size");
    $db->exec("ALTER TABLE dbd_screenshots ADD INDEX IF NOT EXISTS idx_file_created_date (file_created_date)");
    
    // Add hidden column if it doesn't exist (for existing tables)
    $db->exec("ALTER TABLE dbd_screenshots ADD COLUMN IF NOT EXISTS hidden TINYINT DEFAULT 0 AFTER upload_date");
    $db->exec("ALTER TABLE dbd_screenshots ADD INDEX IF NOT EXISTS idx_hidden (hidden)");
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}

// Ensure dbd_users table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS dbd_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        full_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX idx_username (username),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Insert default user if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM dbd_users WHERE username = ?");
    $stmt->execute(['IM QUATRO']);
    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash('112romanialovE', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO dbd_users (username, password, email, full_name) VALUES (?, ?, ?, ?)");
        $stmt->execute(['IM QUATRO', $hashedPassword, 'imquatro@example.com', 'IM QUATRO']);
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}