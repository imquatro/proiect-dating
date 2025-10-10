-- ============================================
-- RESTORE LIGI PENTRU PRODUCTIE (24H)
-- ============================================
-- 
-- Acest script restaureaza ligile pentru productie:
-- - Reseteaza min_players la 64
-- - Pastreaza numele in engleza (Bronze, Platinum, Gold)
--
-- Nota: Timpii se schimba manual in pvp_cron.php (decomentati TIMPI PRODUCTION)
-- ============================================

-- Update min_players inapoi la 64 pentru productie
UPDATE pvp_leagues SET min_players = 64 WHERE level = 1;
UPDATE pvp_leagues SET min_players = 64 WHERE level = 2;
UPDATE pvp_leagues SET min_players = 64 WHERE level = 3;

-- Reseteaza toti userii la Bronze pentru inceput fresh
UPDATE user_league_status SET league_id = 1, qualified_for_league_id = NULL WHERE league_id > 1;

SELECT 'LIGI RESTAURATE PENTRU PRODUCTIE!' AS status;
SELECT * FROM pvp_leagues ORDER BY level;

-- ============================================
-- REMINDER: MODIFICATI SI pvp_cron.php
-- ============================================
-- Decomentati TIMPI PRODUCTION si comentati TIMPI TESTARE:
-- 
-- define('BATTLE_DURATION_MINUTES', 1440);     // 24 ore
-- define('PAUSE_BETWEEN_ROUNDS_MINUTES', 1440); // 24 ore
-- define('LEAGUE_RESET_DAYS', 30);              // Lunar
-- define('FINAL_DISPLAY_MINUTES', 1440);        // 24 ore
-- ============================================

