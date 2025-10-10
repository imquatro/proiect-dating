-- ============================================
-- UPDATE LIGI PENTRU TESTARE RAPIDA PVP
-- ============================================
-- 
-- Acest script actualizeaza ligile existente pentru testare rapida:
-- - Schimba numele: Bronz -> Bronze, Argint -> Silver, Platina -> Platinum
-- - Reduce min_players de la 64 la 32
-- - Adauga liga Gold (nivel 3)
--
-- Pentru a reveni la productie, rulati restore_leagues_production.sql
-- ============================================

-- Update nume ligi si min_players
UPDATE pvp_leagues SET name = 'Bronze', min_players = 32 WHERE level = 1;
UPDATE pvp_leagues SET name = 'Platinum', min_players = 32, color = '#E5E4E2' WHERE level = 2;

-- Sterge liga "Argint" daca exista si creeaza "Gold"
DELETE FROM pvp_leagues WHERE level = 3 AND name != 'Gold';
INSERT IGNORE INTO pvp_leagues (name, level, color, min_players) VALUES ('Gold', 3, '#FFD700', 32);

-- Adauga coloana qualified_for_league_id daca nu exista
ALTER TABLE user_league_status ADD COLUMN IF NOT EXISTS qualified_for_league_id INT DEFAULT NULL AFTER league_id;

-- Adauga coloana completed_at daca nu exista
ALTER TABLE pvp_battles ADD COLUMN IF NOT EXISTS completed_at DATETIME DEFAULT NULL AFTER is_active;

-- Actualizeaza ENUM pentru status daca nu contine 'displaying_final'
ALTER TABLE pvp_battles MODIFY COLUMN status ENUM('pending', 'active', 'completed', 'postponed', 'displaying_final') DEFAULT 'pending';

-- Reseteaza toti userii la Bronze pentru inceput fresh
UPDATE user_league_status SET league_id = 1, qualified_for_league_id = NULL WHERE league_id > 1;

SELECT 'LIGI ACTUALIZATE PENTRU TESTARE!' AS status;
SELECT * FROM pvp_leagues ORDER BY level;

