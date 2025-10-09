// PvP Popup Logic - Trigger la login și la 5 minute înainte de final
(function() {
    const POPUP_COOLDOWN = 60000; // 1 minut cooldown între popup-uri
    const CHECK_INTERVAL = 30000; // Verifică la 30 secunde
    const FINAL_MINUTES_THRESHOLD = 300; // 5 minute în secunde

    let lastPopupTime = 0;
    let checkInterval = null;
    let popupData = null;
    let currentBattleId = null;
    let currentMatchId = null;

    // Verifică dacă trebuie să afișăm popup-ul la login
    function checkLoginPopup() {
        const hasShownPopup = sessionStorage.getItem('pvp_popup_shown');
        
        if (!hasShownPopup) {
            // E prima verificare din această sesiune
            checkAndShowPopup(true);
        }
    }

    // Verifică și arată popup-ul dacă e necesar
    function checkAndShowPopup(isLogin = false) {
        if (!window.userId) return;

        const now = Date.now();
        if (now - lastPopupTime < POPUP_COOLDOWN) {
            return; // Cooldown activ
        }

        fetch('pvp_api.php?action=get_user_current_match')
            .then(r => r.json())
            .then(data => {
                if (data.has_match) {
                    popupData = data;
                    currentBattleId = data.battle_id;
                    currentMatchId = data.match_id;

                    // Afișăm popup la login SAU dacă suntem în ultimele 5 minute
                    if (isLogin || data.is_final_minutes) {
                        showPvpPopup(data);
                        lastPopupTime = now;
                        
                        if (isLogin) {
                            sessionStorage.setItem('pvp_popup_shown', '1');
                        }
                    }
                }
            })
            .catch(err => {
                console.error('Error checking PvP match:', err);
            });
    }

    // Afișează popup-ul cu datele meciului
    function showPvpPopup(data) {
        const popup = document.getElementById('pvp-match-popup');
        if (!popup) return;

        // Update round title
        document.getElementById('popupRoundTitle').textContent = `Meciul tău - ${data.round_name}`;

        // Update avatare și nume
        const userAvatar = data.current_user.photo || 'default-avatar.png';
        const opponentAvatar = data.opponent.photo || 'default-avatar.png';

        document.getElementById('popupUserAvatar').src = userAvatar;
        document.getElementById('popupUserName').textContent = data.current_user.username;
        document.getElementById('popupUserName').className = 'player-name' + (data.current_user.vip ? ' gold-shimmer' : '');

        document.getElementById('popupOpponentAvatar').src = opponentAvatar;
        document.getElementById('popupOpponentName').textContent = data.opponent.username;
        document.getElementById('popupOpponentName').className = 'player-name' + (data.opponent.vip ? ' gold-shimmer' : '');

        // Update score bar
        updateScoreBar(data.user_score, data.opponent_score, data.user_percent, data.opponent_percent);

        // Update mesaj și timer
        const messageEl = document.getElementById('popupMessage');
        const timerEl = document.getElementById('popupTimer');

        if (data.is_final_minutes) {
            messageEl.textContent = 'Meciul tău se încheie în curând!';
            timerEl.style.display = 'block';
            updateTimer(data.time_remaining);
            
            // Start timer pulsant
            startPulsingTimer(data.time_remaining.total_seconds);
        } else {
            messageEl.textContent = 'Meciul se desfășoară ACUM! Urmărește-l live!';
            timerEl.style.display = 'none';
        }

        // Arată popup-ul
        popup.classList.add('active');
        
        // Inițializează chat-ul pentru acest meci
        if (data.match_id && typeof window.initPvpChat === 'function') {
            window.initPvpChat(data.match_id);
        }
        
        // Marchează mesajele ca citite (se face automat în initPvpChat)
    }

    // Update bara de scor (ROȘU stânga, ALBASTRU dreapta)
    function updateScoreBar(userScore, opponentScore, userPercent, opponentPercent) {
        const scoreLeft = document.getElementById('popupScoreLeft');
        const scoreRight = document.getElementById('popupScoreRight');
        const userScoreEl = document.getElementById('popupUserScore');
        const opponentScoreEl = document.getElementById('popupOpponentScore');

        // Update valori
        userScoreEl.textContent = userScore;
        opponentScoreEl.textContent = opponentScore;

        // Update width (roșu = user la stânga, albastru = opponent la dreapta)
        scoreLeft.style.width = userPercent + '%';
        scoreRight.style.width = opponentPercent + '%';

        // Efect "winning" pe scorul mai mare
        if (userScore > opponentScore) {
            scoreLeft.classList.add('winning');
            scoreRight.classList.remove('winning');
        } else if (opponentScore > userScore) {
            scoreRight.classList.add('winning');
            scoreLeft.classList.remove('winning');
        } else {
            scoreLeft.classList.remove('winning');
            scoreRight.classList.remove('winning');
        }
    }

    // Update timer
    function updateTimer(timeRemaining) {
        const timerEl = document.getElementById('popupTimer');
        if (!timerEl) return;

        const { hours, minutes, seconds } = timeRemaining;
        let text = '';

        if (hours > 0) {
            text = `${hours}h ${minutes}m ${seconds}s`;
        } else if (minutes > 0) {
            text = `${minutes}m ${seconds}s`;
        } else {
            text = `${seconds}s`;
        }

        timerEl.textContent = text;
    }

    // Timer cu pulsie (pentru ultimele 5 minute)
    function startPulsingTimer(totalSeconds) {
        let remaining = totalSeconds;
        
        const interval = setInterval(() => {
            remaining--;
            
            if (remaining <= 0) {
                clearInterval(interval);
                closePvpPopup();
                return;
            }

            const hours = Math.floor(remaining / 3600);
            const mins = Math.floor((remaining % 3600) / 60);
            const secs = remaining % 60;

            updateTimer({ hours, minutes: mins, seconds: secs });
        }, 1000);

        // Salvăm interval-ul pentru a-l curăța la close
        popup._timerInterval = interval;
    }

    // Închide popup-ul
    window.closePvpPopup = function() {
        const popup = document.getElementById('pvp-match-popup');
        if (!popup) return;

        popup.classList.remove('active');

        // Curăță timer-ul dacă există
        if (popup._timerInterval) {
            clearInterval(popup._timerInterval);
            popup._timerInterval = null;
        }
        
        // Oprește chat polling
        if (typeof window.stopPvpChat === 'function') {
            window.stopPvpChat();
        }
    };

    // Navighează la pagina battle
    window.goToBattle = function() {
        if (currentBattleId && popupData) {
            closePvpPopup();
            
            // Salvăm datele pentru navigare
            sessionStorage.setItem('pvp_goto_battle', currentBattleId);
            sessionStorage.setItem('pvp_goto_match', currentMatchId);
            sessionStorage.setItem('pvp_goto_league', popupData.current_user?.league_id || 1);
            
            // Redirect
            window.location.href = 'pvp_battles.php';
        } else {
            closePvpPopup();
            window.location.href = 'pvp_battles.php';
        }
    };

    // Listener pentru click pe popup (din pvp.js)
    window.addEventListener('pvp:showMatchPopup', (event) => {
        const match = event.detail;
        
        // Convertim datele din match în formatul așteptat
        const data = {
            battle_id: match.battle_id,
            match_id: match.id,
            round_name: getRoundName(match.round_number),
            current_user: {
                username: window.userId == match.user1_id ? match.user1_name : match.user2_name,
                photo: window.userId == match.user1_id ? match.user1_photo : match.user2_photo,
                vip: window.userId == match.user1_id ? match.user1_vip : match.user2_vip
            },
            opponent: {
                username: window.userId == match.user1_id ? match.user2_name : match.user1_name,
                photo: window.userId == match.user1_id ? match.user2_photo : match.user1_photo,
                vip: window.userId == match.user1_id ? match.user2_vip : match.user1_vip
            },
            user_score: window.userId == match.user1_id ? match.user1_score : match.user2_score,
            opponent_score: window.userId == match.user1_id ? match.user2_score : match.user1_score,
            user_percent: match.user1_percent || 50,
            opponent_percent: match.user2_percent || 50,
            is_final_minutes: false,
            time_remaining: { hours: 0, minutes: 0, seconds: 0, total_seconds: 0 }
        };

        showPvpPopup(data);
    });

    // Listener pentru deschidere chat din indicator top-bar
    window.addEventListener('pvp:openChatPopup', (event) => {
        const data = event.detail;
        showPvpPopup(data);
    });

    // Helper: Nume rundă
    function getRoundName(roundNumber) {
        const names = {
            1: '1/32',
            2: '1/16',
            3: '1/8 (Optimi)',
            4: '1/4 (Sferturi)',
            5: '1/2 (Semifinală)',
            6: 'Finală'
        };
        return names[roundNumber] || `Rundă ${roundNumber}`;
    }

    // Click în afara popup-ului pentru a-l închide
    document.addEventListener('click', (e) => {
        const popup = document.getElementById('pvp-match-popup');
        if (popup && e.target === popup) {
            closePvpPopup();
        }
    });

    // Escape key pentru a închide
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const popup = document.getElementById('pvp-match-popup');
            if (popup && popup.classList.contains('active')) {
                closePvpPopup();
            }
        }
    });

    // Inițializare
    document.addEventListener('DOMContentLoaded', () => {
        // Verificăm la login
        checkLoginPopup();

        // Verificări periodice pentru 5 minute warning
        checkInterval = setInterval(() => {
            checkAndShowPopup(false);
        }, CHECK_INTERVAL);

        // Cleanup la părăsirea paginii
        window.addEventListener('beforeunload', () => {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });

        // Verificăm dacă trebuie să navigăm la un battle specific
        const gotoBattle = sessionStorage.getItem('pvp_goto_battle');
        const gotoMatch = sessionStorage.getItem('pvp_goto_match');
        const gotoLeague = sessionStorage.getItem('pvp_goto_league');

        if (gotoBattle && window.location.pathname.includes('pvp_battles.php')) {
            // Suntem deja pe pagina PvP, scroll la meciul respectiv
            setTimeout(() => {
                const matchCard = document.querySelector(`[data-match-id="${gotoMatch}"]`);
                if (matchCard) {
                    matchCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    matchCard.style.boxShadow = '0 0 30px rgba(255, 215, 0, 1)';
                    setTimeout(() => {
                        matchCard.style.boxShadow = '';
                    }, 2000);
                }

                // Curățăm sessionStorage
                sessionStorage.removeItem('pvp_goto_battle');
                sessionStorage.removeItem('pvp_goto_match');
                sessionStorage.removeItem('pvp_goto_league');
            }, 1000);
        }
    });

    // Reset popup shown flag la logout (dacă există event de logout)
    window.addEventListener('beforeunload', () => {
        // Nu ștergem flag-ul aici pentru că ar apărea la fiecare refresh
        // Flag-ul se șterge automat la închiderea tab-ului (sessionStorage)
    });
})();

