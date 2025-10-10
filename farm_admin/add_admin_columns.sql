-- Add admin level and ban management columns to users table
-- Run this script to add the required columns for the admin grading system

-- Add admin_level column (1=SUPER_ADMIN, 2=ADMIN, 3=MODERATOR, 4=HELPER, 5=USER)
ALTER TABLE users ADD COLUMN admin_level TINYINT(1) NOT NULL DEFAULT 5 
COMMENT 'Admin level: 1=SUPER_ADMIN, 2=ADMIN, 3=MODERATOR, 4=HELPER, 5=USER';

-- Add auto_account column to distinguish auto-created accounts
ALTER TABLE users ADD COLUMN auto_account TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '1 = cont creat din admin panel, 0 = cont creat prin înregistrare normală';

-- Add ban management columns
ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0 
COMMENT '1 = user is banned, 0 = user is active';

ALTER TABLE users ADD COLUMN ban_reason TEXT NULL 
COMMENT 'Reason for ban';

ALTER TABLE users ADD COLUMN ban_end_date DATETIME NULL 
COMMENT 'When ban expires (NULL for permanent)';

ALTER TABLE users ADD COLUMN banned_by VARCHAR(50) NULL 
COMMENT 'Username of admin who banned the user';

ALTER TABLE users ADD COLUMN banned_at DATETIME NULL 
COMMENT 'When the user was banned';

-- Update existing users to have proper admin levels
-- Set current admins (is_admin = 1) to admin_level = 2 (ADMIN)
UPDATE users SET admin_level = 2 WHERE is_admin = 1;

-- Set the first user (quatro) to SUPER_ADMIN (admin_level = 1)
UPDATE users SET admin_level = 1 WHERE id = 1;

-- Set all other users to USER level (admin_level = 5)
UPDATE users SET admin_level = 5 WHERE is_admin = 0;

-- Set all existing users to normal accounts (auto_account = 0)
UPDATE users SET auto_account = 0;

-- Create admin_activity_logs table if it doesn't exist
CREATE TABLE IF NOT EXISTS admin_activity_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    admin_id INT(11) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_user_id INT(11) NULL,
    details TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add indexes for better performance
CREATE INDEX idx_users_admin_level ON users(admin_level);
CREATE INDEX idx_users_auto_account ON users(auto_account);
CREATE INDEX idx_users_is_banned ON users(is_banned);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- Show the updated structure
DESCRIBE users;
