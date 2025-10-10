-- Script pentru crearea tabelului admin_activity_logs
-- Acest tabel va stoca toate acțiunile admin pentru audit și monitoring

CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    admin_username VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_user_id INT NULL,
    target_username VARCHAR(50) NULL,
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    reason TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_admin_id (admin_id),
    INDEX idx_action (action),
    INDEX idx_target_user_id (target_user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adaugă comentariu la tabel
ALTER TABLE admin_activity_logs COMMENT = 'Log-uri pentru toate acțiunile admin în sistem';
