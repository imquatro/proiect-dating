# 🏆 Sistem PvP Battles - Ghid de Setup și Utilizare

## 📋 Prezentare Generală

Sistemul PvP Battles este un sistem competitiv de eliminări între jucători bazat pe **scorul de popularitate**. Jucătorii concurează în 3 ligi (Bronz, Argint, Platină) cu battles automată la fiecare 4 zile.

---

## ✅ Fișiere Create

### Backend PHP:
- `includes/pvp_helpers.php` - Funcții helper și auto-creare tabele
- `pvp_api.php` - API endpoint pentru AJAX
- `pvp_battles.php` - Pagina principală PvP
- `pvp_cron.php` - Cron job pentru automatizare

### Frontend:
- `assets_css/pvp.css` - Stiluri bracket și popup
- `assets_js/pvp.js` - Logica bracket și navigare
- `assets_js/pvp-popup.js` - Logica popup-urilor

### Template:
- `template.php` - MODIFICAT: Adăugat tab PvP + popup VS

---

## 🗄️ Structura Bazei de Date

Tabelele se creează **AUTOMAT** la prima rulare. Nu e nevoie de import SQL manual!

### Tabele create:
1. **pvp_leagues** - Configurare ligi (Bronz/Argint/Platină)
2. **pvp_battles** - Evenimente battle
3. **pvp_participants** - Jucători în battles
4. **pvp_matches** - Meciuri individuale
5. **user_league_status** - Liga curentă a fiecărui user

---

## 🚀 Setup și Configurare

### 1. **Verificare Tabele Existente**

Sistemul verifică automat tabelele necesare pentru calcul popularitate:
- `farm_visits` - pentru vizite la fermă
- `help_records` - pentru ajutoare primite
- `comments` - pentru comentarii
- `messages` - pentru mesaje

**IMPORTANT**: Dacă aceste tabele nu există, trebuie create manual sau scorul de popularitate va fi 0!

### 2. **Configurare Cron Job**

Pentru automatizare completă, adaugă în crontab:

```bash
# Rulează la fiecare oră
0 * * * * php /path/to/htdocs/1/pvp_cron.php

# SAU rulează la fiecare 30 minute
*/30 * * * * php /path/to/htdocs/1/pvp_cron.php
```

**Sau manual pentru testare:**
```bash
php pvp_cron.php
```

### 3. **Prima Rulare - Inițializare**

La prima accesare a paginii `pvp_battles.php`:
1. ✅ Tabelele se creează automat
2. ✅ Ligile (Bronz/Argint/Platină) se inserează automat
3. ✅ Userul curent e alocat automat în Liga Bronz

---

## 🎮 Cum Funcționează Sistemul

### **Ciclul de Battle:**

```
Zi 1-4:   Înscrieri automate (verificare 64 jucători)
Zi 5:     Rundă 1/32 (64→32 jucători)
Zi 6:     Rundă 1/16 (32→16)
Zi 7:     Rundă 1/8 (16→8) - Optimi
Zi 8:     Rundă 1/4 (8→4) - Sferturi
Zi 9:     Rundă 1/2 (4→2) - Semifinale
Zi 10:    Finală (2→1)
→ Repeat la 4 zile
```

### **Sistemul de Ligi:**

- **🥉 Bronz** (Liga 1) - Începători
- **🥈 Argint** (Liga 2) - Intermediari  
- **🥇 Platină** (Liga 3) - Avansați

**Promovare**: Top 4 (semifinaliști + finaliști) urcă o ligă

**Reset Lunar**: Prima zi a lunii, toți jucătorii revin la Liga Bronz

### **Calcul Scor Popularitate:**

```php
Vizite la fermă (ultimele 7 zile): 2 puncte/vizită
Ajutoare primite (ultimele 7 zile): 3 puncte/ajutor
Comentarii primite (ultimele 7 zile): 2 puncte/comentariu
Mesaje trimise (ultimele 7 zile): 1 punct/mesaj
```

Jucătorul cu **scorul mai mare câștigă meciul**.

---

## 🔔 Popup-uri și Notificări

### **Popup VS apare în 2 situații:**

1. **La login nou** (după logout/expirare sesiune):
   - Arată meciul curent al jucătorului
   - Bara de scor LIVE (roșu vs albastru)
   - Buton "Vezi Meciul"

2. **5 minute înainte de finalizare rundă**:
   - Timer cu efect de pulsie
   - Mesaj urgent: "Meciul tău se încheie în curând!"

**Design Popup:**
- Stânga = ROȘU (user)
- Dreapta = ALBASTRU (adversar)
- Bara mai colorată = scor mai mare
- Click oriunde → navighează la battle

---

## 🛠️ Funcții Principale

### **includes/pvp_helpers.php:**

```php
calculatePopularityScore($userId)     // Calculează scorul popularitate
checkMinimumPlayers($leagueId)        // Verifică minim 64 jucători
allocatePlayers($battleId, $leagueId) // Alocă 64 jucători random
createFirstRoundMatches($battleId)    // Creează 32 meciuri (1/32)
processRoundResults($battleId, $round)// Procesează câștigători și creează runda următoare
promoteTopPlayers($battleId)          // Promovează top 4 la ligă superioară
monthlyLeagueReset()                  // Reset lunar - toți în Bronz
getUserBattleStatus($userId)          // Status battle curent user
```

### **pvp_api.php - Endpoints:**

```
GET pvp_api.php?action=get_battle_status
    → Status general battle (timer, rundă, meci curent)

GET pvp_api.php?action=get_league_battles&league_id=1
    → Toate battles pentru o ligă

GET pvp_api.php?action=get_bracket&battle_id=1&round=2
    → Bracket complet sau rundă specifică

GET pvp_api.php?action=get_match_details&match_id=1
    → Detalii complete meci (scoruri, jucători)

GET pvp_api.php?action=get_user_current_match
    → Meciul curent al userului (pentru popup)

GET pvp_api.php?action=get_all_leagues
    → Lista tuturor ligilor
```

---

## 🎨 Interfață și Navigare

### **Tab PvP în Template:**
- Icon: `<i class="fas fa-trophy"></i>`
- Poziție: Entre Friends și VIP
- Active state: Consistent cu celelalte tabs

### **Pagina pvp_battles.php:**
- **Header**: Timer live + Status rundă + Liga curentă
- **Tabs Ligi**: Bronz | Argint | Platină
- **Sub-tabs Runde**: 1/32 | 1/16 | 1/8 | 1/4 | Semi | Finală
- **Bracket**: Cards cu meciuri (avatar + nume + scor)
- **Click pe meci** → Popup cu detalii

### **Design Consistent:**
- Background: `url('../img/bg2.png')`
- Culori: `#ffe9a3`, `#f6cf49`, `#6c4e09`
- Shadow: `0 0 30px rgba(255, 255, 255, 0.6)`

---

## 🐛 Troubleshooting

### **Problema: Battles nu pornesc automat**
✅ **Soluție**: 
- Verifică cron job: `crontab -l`
- Rulează manual: `php pvp_cron.php`
- Check logs: `tail -f /var/log/apache2/error.log`

### **Problema: Jucători insuficienți (<64)**
✅ **Soluție**:
- Battle-ul va fi marcat ca "postponed"
- Implementați sistemul de useri fake (viitor)
- Sau reduceți `min_players` în tabela `pvp_leagues`

### **Problema: Scorul de popularitate e 0**
✅ **Soluție**:
- Verifică tabelele: `farm_visits`, `help_records`, `comments`, `messages`
- Creează date de test în aceste tabele
- Verifică foreign keys și relații

### **Problema: Popup-ul nu apare**
✅ **Soluție**:
- Check console browser: `F12` → Console
- Verifică sesiunea: `sessionStorage.getItem('pvp_popup_shown')`
- Clear storage: `sessionStorage.clear()`
- Verifică dacă userul are meci activ: API `get_user_current_match`

---

## 📊 Testare și Debugging

### **1. Test Creare Battle Manual:**

```php
// Rulează în browser sau CLI
require_once 'includes/pvp_helpers.php';

$leagueId = 1; // Bronz
$db->prepare("INSERT INTO pvp_battles (league_id, start_date, current_round, status, is_active) VALUES (?, NOW(), 1, 'active', 1)")->execute([$leagueId]);
$battleId = $db->lastInsertId();

allocatePlayers($battleId, $leagueId);
createFirstRoundMatches($battleId);

echo "Battle #$battleId creat!";
```

### **2. Test Popup:**

```javascript
// În browser console
fetch('pvp_api.php?action=get_user_current_match')
  .then(r => r.json())
  .then(data => console.log(data));
```

### **3. Simulare Avansare Rundă:**

```php
// pvp_cron.php - linia 89
$expectedRound = max(1, $daysSinceStart - 3);
// Schimbă temporar în:
$expectedRound = $currentRound + 1; // Forțează avansare
```

---

## 🔐 Securitate

- ✅ Toate query-urile folosesc **prepared statements**
- ✅ Verificare `$_SESSION['user_id']` în toate endpoint-urile
- ✅ Sanitizare output cu `htmlspecialchars()`
- ✅ No SQL injection possible

---

## 🚧 Viitoare Îmbunătățiri (TODO)

- [ ] Sistem useri fake pentru completare 64 jucători
- [ ] Sistem de premii (monede, gold, achievements)
- [ ] Istoric battles (top 10 câștigători all-time)
- [ ] Notificări push pentru meciuri
- [ ] Chat live în timpul meciului
- [ ] Animații pe bracket (trasee colorate)
- [ ] Statistici detaliate (win rate, avg score)

---

## 📞 Support

Pentru probleme sau întrebări:
1. Check acest README
2. Verifică logs: `error_log()` în PHP
3. Browser console pentru JS errors
4. Database check: `SELECT * FROM pvp_battles WHERE is_active = 1`

---

**Sistem implementat cu succes! Enjoy the battles! 🏆⚔️**

