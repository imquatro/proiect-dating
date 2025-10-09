<?php
// Create user_preferences table for storing loading style preferences

require_once __DIR__ . '/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        loading_style VARCHAR(20) DEFAULT 'variant-1',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($sql);
    echo "✓ Table user_preferences created successfully\n";
} catch (PDOException $e) {
    echo "✗ Error creating table: " . $e->getMessage() . "\n";
}
?>

