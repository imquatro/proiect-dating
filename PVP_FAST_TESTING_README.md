# PVP Fast Testing Setup

## 📋 Overview

Sistemul PVP a fost configurat pentru **testare rapidă** cu următoarele setări:

### ⏱️ Timpi Testare (CURRENT):
- **Auto-start:** IMEDIAT când sunt 32+ jucători în ligă
- **Durată rundă:** 5 minute
- **Pauză între runde:** 1 minut
- **Afișare finală:** 5 minute (cu efecte winner/loser)
- **Reset ligi:** La fiecare 2 zile (toți revin în Bronze)

### 🏆 Sistem Ligi:
1. **Bronze** (Liga 1) - Începători
2. **Platinum** (Liga 2) - Intermediari
3. **Gold** (Liga 3) - Avansați

### 📊 Promovare:
- **Top 4 semifinaliști** sunt marcați "qualified" pentru liga superioară
- Joacă în continuare în liga curentă până când liga superioară are 32+ jucători calificați
- Când liga superioară ajunge la 32+, toți jucătorii calificați sunt promovați automat

---

## 🚀 Setup Pentru Testare

### 1. Rulați SQL Update:
```bash
mysql -u root -p datingz1 < update_leagues_for_testing.sql
```

**SAU** rulați manual în phpMyAdmin:
- Deschide `update_leagues_for_testing.sql`
- Executați tot conținutul

### 2. Verificați Ligile:
```sql
SELECT * FROM pvp_leagues ORDER BY level;
```

Ar trebui să vedeți:
```
| id | name     | level | color   | min_players |
|----|----------|-------|---------|-------------|
| 1  | Bronze   | 1     | #CD7F32 | 32          |
| 2  | Platinum | 2     | #E5E4E2 | 32          |
| 3  | Gold     | 3     | #FFD700 | 32          |
```

### 3. Adaugați Jucători de Test:
- Mergi la **Admin Panel → User Creation → Auto-Create Users**
- Creează **32+ useri** pentru a porni primul battle

### 4. Setup CRON (Important!):
```bash
# Editați crontab
crontab -e

# Adaugați (rulare la fiecare minut):
* * * * * php /path/to/pvp_cron.php >> /path/to/pvp_cron.log 2>&1
```

**SAU** rulați manual pentru testare:
```bash
php pvp_cron.php
```

---

## 🧪 Flow Testare

### Pasul 1: Adaugă 32+ Useri
```
Admin Panel → User Creation → Auto-Create: 35 users
```

### Pasul 2: Verifică Auto-Enrollment
```sql
SELECT COUNT(*), league_id FROM user_league_status GROUP BY league_id;
```

Toți userii ar trebui să fie în liga 1 (Bronze).

### Pasul 3: Pornește CRON
```bash
php pvp_cron.php
```

Loguri așteptate:
```
[timestamp] === PvP Cron Job Started ===
[timestamp] ✓ Liga Bronze: 35 jucatori! Incepem battle IMEDIAT...
[timestamp] Battle #1 creat pentru liga Bronze
[timestamp] 32 jucatori alocati in battle #1
[timestamp] 16 meciuri create pentru runda 1/16
[timestamp] ✓ Battle #1 initiat cu succes pentru liga Bronze!
```

### Pasul 4: Verifică Battle Activ
```sql
SELECT * FROM pvp_battles WHERE is_active = 1;
SELECT * FROM pvp_matches WHERE battle_id = 1 AND round_number = 1;
```

### Pasul 5: Așteaptă Progresie
- **Runda 1 (1/16):** 5 min + 1 min pauză = 6 min
- **Runda 2 (1/8):** 5 min + 1 min pauză = 6 min
- **Runda 3 (1/4):** 5 min + 1 min pauză = 6 min
- **Runda 4 (Semi):** 5 min + 1 min pauză = 6 min
- **Runda 5 (Final):** 5 min + 5 min afișare = 10 min
- **TOTAL:** ~34 minute per battle complet

### Pasul 6: Verifică Promovări
După finală, verifică:
```sql
-- Jucatori calificati pentru Platinum
SELECT COUNT(*) FROM user_league_status WHERE qualified_for_league_id = 2;

-- Ar trebui sa fie 4 (semifinalistii)
```

### Pasul 7: Testează Reset (După 2 Zile)
```sql
-- Forteaza reset manual pentru testare
UPDATE user_league_status SET last_reset_date = DATE_SUB(CURDATE(), INTERVAL 3 DAY);

-- Ruleaza cron
php pvp_cron.php

-- Verifica ca toti sunt inapoi in Bronze
SELECT COUNT(*), league_id FROM user_league_status GROUP BY league_id;
```

---

## 🔄 Restore la Production

### Când ești gata să revii la timpi normali (24h):

1. **Rulați SQL Restore:**
```bash
mysql -u root -p datingz1 < restore_leagues_production.sql
```

2. **Editați `pvp_cron.php`:**

Comentați TIMPI TESTARE:
```php
// TIMPI ACTUALI (TESTARE) - COMENTAT PENTRU PRODUCTIE
// define('BATTLE_DURATION_MINUTES', 5);
// define('PAUSE_BETWEEN_ROUNDS_MINUTES', 1);
// define('LEAGUE_RESET_DAYS', 2);
// define('FINAL_DISPLAY_MINUTES', 5);
```

Decomentați TIMPI PRODUCTION:
```php
// TIMPI PRODUCTION (DECOMENTATE)
define('BATTLE_DURATION_MINUTES', 1440);     // 24 ore
define('PAUSE_BETWEEN_ROUNDS_MINUTES', 1440); // 24 ore
define('LEAGUE_RESET_DAYS', 30);              // Lunar
define('FINAL_DISPLAY_MINUTES', 1440);        // 24 ore
```

3. **Verificați:**
```bash
php pvp_cron.php
```

---

## 🐛 Debugging

### Verifică Loguri CRON:
```bash
tail -f /path/to/pvp_cron.log
```

### Verifică Battle Status:
```sql
SELECT 
    b.id,
    l.name as league,
    b.current_round,
    b.status,
    b.start_date,
    TIMESTAMPDIFF(MINUTE, b.start_date, NOW()) as minutes_elapsed
FROM pvp_battles b
JOIN pvp_leagues l ON b.league_id = l.id
WHERE b.is_active = 1;
```

### Verifică Meciuri Active:
```sql
SELECT 
    m.id,
    m.round_number,
    u1.username as player1,
    u2.username as player2,
    m.user1_score,
    m.user2_score,
    m.winner_id,
    m.completed
FROM pvp_matches m
JOIN users u1 ON m.user1_id = u1.id
JOIN users u2 ON m.user2_id = u2.id
WHERE m.battle_id = 1 AND m.completed = 0;
```

### Force Advance Round (Manual):
```sql
-- DOAR PENTRU DEBUG! NU FOLOSI IN PRODUCTIE!
UPDATE pvp_battles SET start_date = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE id = 1;
```

---

## 📞 Support

Dacă întâmpini probleme:
1. Verifică logurile CRON
2. Verifică că min_players = 32 în pvp_leagues
3. Verifică că toți userii au intrare în user_league_status
4. Rulează `php pvp_cron.php` manual pentru debugging

---

**Succes la testare! 🎮🏆**

