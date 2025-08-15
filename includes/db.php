<?php
require_once __DIR__ . '/config.php';

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

// Ensure `level` column exists in `users` table
try {
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'level'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE users ADD COLUMN level INT(11) NOT NULL DEFAULT 1");
    }
} catch (PDOException $e) {
    // ignore if insufficient privileges or other errors
}
