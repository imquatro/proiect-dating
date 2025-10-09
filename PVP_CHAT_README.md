# 💬 PvP Chat Live - Documentație Completă

## 🎯 Prezentare Generală

**Chat Live efemer** integrat în sistemul PvP Battles - permite jucătorilor să comunice în timp real în timpul meciurilor!

---

## ✅ Fișiere Create/Modificate

### **Noi (7 fișiere):**
1. `pvp_chat_api.php` - API pentru mesaje chat
2. `assets_css/pvp-chat.css` - Stiluri chat + notificări
3. `assets_js/pvp-chat.js` - Logica chat + polling

### **Modificate (4 fișiere):**
4. `includes/pvp_helpers.php` - Tabel + funcții helper chat
5. `template.php` - Indicator top-bar + chat UI în popup
6. `assets_js/pvp.js` - Butoane popup manual + badges
7. `assets_js/pvp-popup.js` - Integrare chat în popup

---

## 🗄️ Structura Database

### **Tabel Nou: `pvp_match_chat`**
```sql
CREATE TABLE pvp_match_chat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES pvp_matches(id) ON DELETE CASCADE,
    INDEX (match_id, created_at),
    INDEX (match_id, is_read)
);
```

**Caracteristici:**
- ✅ Se creează AUTOMAT la prima accesare
- ✅ Mesajele se ȘTERG automat când meciul se finalizează
- ✅ Index-uri pentru performanță

---

## 🎨 Interfață și Design

### **1. Indicator Top-Bar (ca messageIndicator)**
```
┌────────────────────────────────┐
│ [≡] [✉️²] [💬¹] [$$$] [👤]    │
│         ↑mesaje ↑PvP chat      │
└────────────────────────────────┘
```

**Funcționalitate:**
- Bulină verde pulsantă când ai mesaje necitite
- Counter cu numărul de mesaje
- Click → Deschide popup-ul meciului tău

### **2. Chat în Popup VS**
```
┌─────────────────────────────────────┐
│  Meciul tău - 1/16          [×]     │
├─────────────────────────────────────┤
│  [👤] Avatar    VS    Avatar [👤]   │
├─────────────────────────────────────┤
│  [████████Roșu░░░░Albastru]        │
│     1250              850           │
├─────────────────────────────────────┤
│  💬 Chat Live:                [²]   │
│  ┌─────────────────────────────────┐│
│  │ Adversar: Hai noroc! 🔥        ││
│  │ Tu: La fel bro! 💪             ││
│  │ Adversar: Să câștige cel mai   ││
│  │            popular! 😎          ││
│  └─────────────────────────────────┘│
│  [Scrie mesaj...] [😊] [📤]        │
├─────────────────────────────────────┤
│  [Vezi Meciul în Bracket]           │
└─────────────────────────────────────┘
```

**Features:**
- ✅ Mesaje proprii: Bubble verde (dreapta)
- ✅ Mesaje adversar: Bubble albastru (stânga)
- ✅ Auto-scroll la mesaj nou
- ✅ Emoji picker (8 emoji-uri rapide)
- ✅ Badge counter mesaje necitite

### **3. Buton Popup Manual pe Match Card**
```
┌─────────────────────────────────┐
│  Meci #123              [LIVE]  │
│  ┌────┐  VS  ┌────┐            │
│  │ 👤 │      │ 👤 │            │
│  └────┘      └────┘            │
│   1250        850               │
│                                 │
│  [💬² Vezi VS & Chat]           │
└─────────────────────────────────┘
```

**Când apare:**
- ✅ DOAR pe meciul userului curent
- ✅ DOAR dacă meciul nu e finalizat
- ✅ Badge roșu pulsant dacă mesaje necitite

### **4. Badge-uri Pulsante**

**Top-Bar Indicator:**
```css
.pvp-chat-indicator {
    background: #4caf50;
    border-radius: 50%;
    animation: pvpChatPulse 2s ease-in-out infinite;
}
```

**Tab Ligă:**
```
[🥉 Bronz] [🥈 Argint💬²] [🥇 Platină]
                    ↑ badge
```

**Tab Rundă:**
```
[1/32] [1/16💬²] [1/8] [1/4]
           ↑ badge
```

---

## 🔔 Sistem Notificări

### **Flow Complet:**

```
1. Adversarul trimite mesaj
   ↓
2. Backend salvează în pvp_match_chat (is_read = 0)
   ↓
3. Polling (5 sec) detectează mesaj nou
   ↓
4. Apare badge pulsant în:
   - ✅ Top-bar indicator (💬¹)
   - ✅ Tab ligă (🥈 Argint💬¹)
   - ✅ Tab rundă (1/16💬¹)
   - ✅ Buton popup card (💬² Vezi VS & Chat)
   ↓
5. User deschide popup (oricând - auto/manual)
   ↓
6. Chat se inițializează (initPvpChat)
   ↓
7. Mesajele sunt marcate ca citite automat
   ↓
8. TOATE badge-urile dispar
```

### **Trigger-uri Deschidere Popup:**

#### **A. Automat:**
1. **La login nou** (după logout/expirare sesiune)
2. **5 minute înainte** de finalizare rundă (timer pulsant)

#### **B. Manual:**
1. **Click pe indicator top-bar** (💬¹)
2. **Click pe buton în match card** (Vezi VS & Chat)
3. **Click pe card-ul meciului** (doar vizualizare, fără chat)

---

## 🛡️ Limitări și Securitate

### **Anti-Spam:**
- ✅ **Max 100 caractere** per mesaj
- ✅ **Cooldown 5 secunde** între mesaje
- ✅ **Max 30 mesaje** per user per meci
- ✅ Verificare dacă userul participă la meci

### **Validări Backend:**
```php
// pvp_chat_api.php
if (strlen($message) > 100) {
    return error('Mesajul este prea lung');
}

if (($now - $lastTime) < 5) {
    return error('Așteaptă X secunde');
}

if ($count >= 30) {
    return error('Ai atins limita de mesaje');
}
```

### **Auto-Cleanup:**
Mesajele se șterg automat când:
- Meciul se finalizează (completed = 1)
- Rulează cron job (pvp_cron.php)

---

## 🔧 API Endpoints

### **1. Trimite Mesaj**
```javascript
POST pvp_chat_api.php
{
    action: 'send_message',
    match_id: 123,
    message: 'Salut! 🔥'
}

Response:
{
    success: true,
    message_id: 456
}
```

### **2. Obține Mesaje**
```javascript
GET pvp_chat_api.php?action=get_messages&match_id=123

Response:
{
    messages: [
        {
            id: 1,
            user_id: 2,
            username: 'Adversar',
            photo: 'avatar.jpg',
            vip: 1,
            message: 'Hai noroc!',
            is_own_message: 0,
            created_at: '2025-10-08 14:30:00'
        },
        // ...
    ],
    total: 5
}
```

### **3. Marchează ca Citit**
```javascript
POST pvp_chat_api.php
{
    action: 'mark_read',
    match_id: 123
}

Response:
{
    success: true,
    marked: 3
}
```

### **4. Număr Mesaje Necitite**
```javascript
GET pvp_chat_api.php?action=get_unread_count

Response:
{
    unread_count: 2,
    match_id: 123,
    opponent_id: 5
}
```

---

## ⚙️ Funcții JavaScript Principale

### **assets_js/pvp-chat.js:**

```javascript
// Pornește chat pentru un meci
window.initPvpChat(matchId)

// Oprește chat polling
window.stopPvpChat()

// Trimite mesaj
window.sendChatMessage()

// Toggle emoji picker
window.toggleEmojiPicker()

// Insert emoji
window.insertEmoji(emoji)

// Deschide popup din top-bar
window.openPvpChatFromIndicator()
```

### **assets_js/pvp.js:**

```javascript
// Deschide popup manual din buton
window.openMatchPopupManual(event, matchId)

// Update badges (triggered prin event)
window.addEventListener('pvp:updateChatBadge', ...)
```

### **assets_js/pvp-popup.js:**

```javascript
// Listeners pentru events
window.addEventListener('pvp:showMatchPopup', ...)
window.addEventListener('pvp:openChatPopup', ...)
```

---

## 🎯 Polling și Performance

### **Chat Polling (3 secunde):**
```javascript
// În pvp-chat.js
chatPollInterval = setInterval(loadChatMessages, 3000);
```

**Optimizări:**
- Polling pornește DOAR când popup-ul e deschis
- Se oprește automat la închidere popup
- Verifică lastMessageId pentru a evita re-render inutil

### **Notificări Polling (5 secunde):**
```javascript
// În pvp-chat.js
notificationPollInterval = setInterval(updateChatNotifications, 5000);
```

**Optimizări:**
- Rulează în background permanent (pentru top-bar)
- Request simplu (doar counter)
- Update doar dacă count-ul s-a schimbat

---

## 🎨 Stiluri și Animații

### **Pulse Animation (badge-uri):**
```css
@keyframes pvpChatPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}
```

### **Message Slide In:**
```css
@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

### **Culori Mesaje:**
- **Mesaje proprii**: Gradient verde `#4caf50 → #45a049`
- **Mesaje adversar**: Gradient albastru `#2196f3 → #1976d2`

---

## 📱 Responsive Design

### **Mobile (<480px):**
```css
.match-popup-chat {
    max-height: 200px;
}

.chat-messages {
    max-height: 120px;
}

.emoji-picker {
    grid-template-columns: repeat(3, 1fr);
}
```

---

## 🧪 Testare

### **1. Test Chat Manual:**

**Browser Console:**
```javascript
// Pornește chat pentru meciul #123
window.initPvpChat(123);

// Trimite mesaj
document.getElementById('chatInput').value = 'Test mesaj';
window.sendChatMessage();

// Verifică mesaje
fetch('pvp_chat_api.php?action=get_messages&match_id=123')
    .then(r => r.json())
    .then(data => console.log(data));
```

### **2. Test Notificări:**

```javascript
// Verifică count mesaje necitite
fetch('pvp_chat_api.php?action=get_unread_count')
    .then(r => r.json())
    .then(data => console.log(data));

// Trigger manual update badges
window.dispatchEvent(new CustomEvent('pvp:updateChatBadge', {
    detail: { count: 5, matchId: 123 }
}));
```

### **3. Test Butoane Popup:**

```javascript
// Deschide popup din top-bar
window.openPvpChatFromIndicator();

// Deschide popup din buton card
window.openMatchPopupManual(event, 123);
```

---

## 🔥 Features Highlight

### ✅ **Ce Funcționează Perfect:**
1. Chat efemer (se șterge după meci)
2. Indicator top-bar cu badge pulsant
3. Badge-uri pe tabs ligă și rundă
4. Buton popup manual pe match card
5. Polling LIVE pentru mesaje (3 sec)
6. Polling notificări (5 sec)
7. Mark as read automat la deschidere
8. Emoji picker (8 emoji-uri)
9. Anti-spam (cooldown + limite)
10. Responsive design

### ✅ **Flow-uri Suportate:**
- Login nou → Popup automat cu chat
- 5 min înainte → Popup cu timer + chat
- Click top-bar → Deschide popup
- Click buton card → Deschide popup
- Click în popup → Navighează la bracket

---

## 🚀 Cum să Folosești

### **Pentru Jucători:**

1. **Primești mesaj** → Badge verde pulsant în top-bar
2. **Click pe badge** → Popup se deschide automat
3. **Vezi chat-ul** → Mesajele sunt marcate ca citite
4. **Răspunzi** → Scrie mesaj + trimite (sau emoji)
5. **Adversarul vede** → Badge la el + mesajul tău

### **Pentru Admini:**

**Cleanup manual mesaje vechi:**
```sql
DELETE FROM pvp_match_chat 
WHERE match_id IN (
    SELECT id FROM pvp_matches 
    WHERE completed = 1
);
```

**Statistici chat:**
```sql
-- Top 10 cei mai activi în chat
SELECT user_id, COUNT(*) as total_messages 
FROM pvp_match_chat 
GROUP BY user_id 
ORDER BY total_messages DESC 
LIMIT 10;
```

---

## 🎯 Rezumat Final

**Sistemul PvP Chat Live este:**
- ✅ **Complet funcțional** - toate feature-urile implementate
- ✅ **Efemer** - mesajele se șterg automat
- ✅ **Securizat** - validări, limite, anti-spam
- ✅ **Performant** - polling optimizat, index-uri DB
- ✅ **Integrat perfect** - design consistent cu site-ul
- ✅ **Notificări complete** - badge-uri pulsante peste tot

**Enjoy the trash talk! 💬🔥**

