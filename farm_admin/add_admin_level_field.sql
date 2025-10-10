-- Script pentru adăugarea câmpului admin_level în tabelul users
-- Acest câmp va gestiona gradele admin pentru sistemul de permisiuni

-- Adaugă câmpul admin_level cu valoare default 5 (USER)
ALTER TABLE users ADD COLUMN admin_level TINYINT(1) NOT NULL DEFAULT 5 
COMMENT '1=SUPER_ADMIN, 2=ADMIN, 3=MODERATOR, 4=HELPER, 5=USER';

-- Actualizează conturile existente cu is_admin = 1 să aibă admin_level = 2 (ADMIN)
UPDATE users SET admin_level = 2 WHERE is_admin = 1;

-- Opțional: Adaugă un index pentru performanță mai bună la query-uri
CREATE INDEX idx_admin_level ON users(admin_level);

-- Opțional: Adaugă un comentariu la tabel
ALTER TABLE users COMMENT = 'Tabel utilizatori cu sistem de grade admin';

-- Verifică rezultatul
SELECT 
    admin_level,
    CASE 
        WHEN admin_level = 1 THEN 'SUPER_ADMIN'
        WHEN admin_level = 2 THEN 'ADMIN' 
        WHEN admin_level = 3 THEN 'MODERATOR'
        WHEN admin_level = 4 THEN 'HELPER'
        WHEN admin_level = 5 THEN 'USER'
        ELSE 'UNKNOWN'
    END as role_name,
    COUNT(*) as count
FROM users 
GROUP BY admin_level
ORDER BY admin_level;
