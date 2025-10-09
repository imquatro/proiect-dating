document.addEventListener('DOMContentLoaded', () => {
    const pvpData = document.getElementById('pvpData');
    if (!pvpData) return;

    const userId = pvpData.dataset.userId;
    const userLeagueId = parseInt(pvpData.dataset.userLeagueId);
    
    let currentLeagueId = userLeagueId;
    let currentBattleId = null;
    let currentRound = 1;
    let updateInterval = null;

    // Funcții helper
    function formatTime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (days > 0) {
            return `${days}d ${hours}h ${mins}m`;
        } else if (hours > 0) {
            return `${hours}h ${mins}m ${secs}s`;
        } else if (mins > 0) {
            return `${mins}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    }

    function getDefaultAvatar() {
        return 'default-avatar.png';
    }

    // Update timer și status
    function updateBattleStatus() {
        fetch(`pvp_api.php?action=get_battle_status`)
            .then(r => r.json())
            .then(data => {
                const timerEl = document.getElementById('timerText');
                const statusEl = document.getElementById('pvpStatus');

                if (data.has_active_battle) {
                    const timeRemaining = data.time_remaining;
                    timerEl.textContent = formatTime(timeRemaining.total_seconds);
                    
                    const roundName = getRoundName(data.current_round);
                    statusEl.textContent = `Rundă: ${roundName}`;

                    if (data.is_eliminated) {
                        statusEl.textContent += ' (Eliminat)';
                        statusEl.style.color = '#ff4444';
                    }

                    currentBattleId = data.battle_id;
                    currentRound = data.current_round;

                    // Highlight runda activă
                    highlightActiveRound(currentLeagueId, data.current_round);
                } else {
                    timerEl.textContent = 'Nu ai battle activ';
                    statusEl.textContent = 'Următorul battle va începe în curând!';
                }
            })
            .catch(err => {
                console.error('Error updating battle status:', err);
            });
    }

    function getRoundName(roundNumber) {
        const names = {
            1: '1/32',
            2: '1/16',
            3: '1/8',
            4: '1/4 (Semifinală)',
            5: 'Finală'
        };
        return names[roundNumber] || `Rundă ${roundNumber}`;
    }

    function highlightActiveRound(leagueId, roundNumber) {
        const container = document.getElementById(`roundTabs-${leagueId}`);
        if (!container) return;

        const buttons = container.querySelectorAll('.round-btn');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (parseInt(btn.dataset.round) === roundNumber) {
                btn.classList.add('active');
            }
        });
    }

    // Load bracket pentru o ligă
    function loadBracket(leagueId, roundNumber = 0) {
        const loadingEl = document.getElementById(`loading-${leagueId}`);
        const bracketEl = document.getElementById(`bracket-${leagueId}`);
        const noBattleEl = document.getElementById(`noBattle-${leagueId}`);

        // Ascunde loading-ul imediat
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        
        // Afișează bracket-ul
        if (bracketEl) {
            bracketEl.style.display = 'block';
        }
        
        // Ascunde mesajul de "no battle"
        if (noBattleEl) {
            noBattleEl.style.display = 'none';
        }

        // Show visual bracket for specific round or first round
        if (bracketEl) {
            const roundToShow = roundNumber > 0 ? roundNumber : 1;
            renderTournamentTable(bracketEl, roundToShow);
        }
    }

    function loadBracketData(battleId, leagueId, roundNumber = 0) {
        const loadingEl = document.getElementById(`loading-${leagueId}`);
        const bracketEl = document.getElementById(`bracket-${leagueId}`);

        // Ascunde loading-ul imediat și afișează bracket-ul vizual
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        
                if (bracketEl) {
                    bracketEl.style.display = 'block';
            // Afișează bracket-ul vizual direct, fără să aștepte datele din API
            renderTournamentTable(bracketEl, roundNumber > 0 ? roundNumber : 1);
                }
    }

    function renderBracket(bracket, container, specificRound = 0) {
        // În loc să proceseze datele din API, afișează direct bracket-ul vizual
        renderTournamentTable(container, specificRound > 0 ? specificRound : 1);
    }

    function renderRound(roundNum, matches, container) {
        const roundDiv = document.createElement('div');
        roundDiv.className = 'bracket-round';
        roundDiv.innerHTML = `<div class="bracket-round-title">${getRoundName(parseInt(roundNum))}</div>`;

        const matchesDiv = document.createElement('div');
        matchesDiv.className = 'bracket-matches';

        matches.forEach(match => {
            const matchCard = createMatchCard(match);
            matchesDiv.appendChild(matchCard);
        });

        roundDiv.appendChild(matchesDiv);
        container.appendChild(roundDiv);
    }

    // Generează scheletul gol pentru toate rundele
    function renderEmptyBracket(container) {
        // Afișează direct bracket-ul vizual pentru prima rundă
        renderTournamentTable(container, 1);
    }

    // Generează bracket-ul complet cu toate rundele - stil vizual ca în poză
    function renderCompleteBracket(container) {
        container.innerHTML = '';
        
        const completeBracket = document.createElement('div');
        completeBracket.className = 'complete-bracket';
        
        // Definim rundele cu numărul de meciuri
        const rounds = [
            { num: 1, matches: 32, title: '1/32' },
            { num: 2, matches: 16, title: '1/16' },
            { num: 3, matches: 8, title: '1/8' },
            { num: 4, matches: 4, title: '1/4' },
            { num: 5, matches: 2, title: 'Semi' },
            { num: 6, matches: 1, title: 'Final' }
        ];
        
        rounds.forEach(round => {
            const roundDiv = createBracketRound(round);
            completeBracket.appendChild(roundDiv);
        });
        
        // Adaugă liniile de conexiune între rundele
        addCompleteBracketConnections(completeBracket, rounds);
        
        container.appendChild(completeBracket);
    }

    // Generează tabelul de turnament ca în poză
    function renderTournamentTable(container, roundNum) {
        container.innerHTML = '';
        
        const roundData = {
            num: roundNum,
            matches: getMatchesForRound(roundNum),
            title: getRoundName(roundNum)
        };
        
        // Creează container-ul principal pentru tabelul de turnament
        const tournamentContainer = document.createElement('div');
        tournamentContainer.className = 'tournament-table-container';
        
        // Titlul rundei (bară orizontală ca în poză)
        const roundTitle = document.createElement('div');
        roundTitle.className = 'tournament-round-title';
        roundTitle.textContent = roundData.title;
        tournamentContainer.appendChild(roundTitle);
        
        // Pentru finala, afișează altfel
        if (roundNum === 5) {
            const finalCard = createFinalMatchCard();
            tournamentContainer.appendChild(finalCard);
        } else {
            // Pentru toate celelalte runde, creează tabelul cu două grupe
            const tournamentTable = createTournamentTable(roundData);
            tournamentContainer.appendChild(tournamentTable);
        }
        
        container.appendChild(tournamentContainer);
    }

    // Creează tabelul de turnament cu două grupe (stânga-dreapta)
    function createTournamentTable(roundData) {
        const tableContainer = document.createElement('div');
        tableContainer.className = 'tournament-table';
        
        // Calculăm câte meciuri pe fiecare parte
        const totalMatches = roundData.matches;
        const matchesPerSide = Math.ceil(totalMatches / 2);
        
        // Grupa stângă
        const leftGroup = document.createElement('div');
        leftGroup.className = 'tournament-group left-group';
        
        // Grupa dreaptă
        const rightGroup = document.createElement('div');
        rightGroup.className = 'tournament-group right-group';
        
        // Generează sloturile pentru grupa stângă
        for (let i = 0; i < matchesPerSide; i++) {
            const slotCard = createSlotCard(i + 1, roundData.num);
            leftGroup.appendChild(slotCard);
        }
        
        // Generează sloturile pentru grupa dreaptă
        for (let i = matchesPerSide; i < totalMatches; i++) {
            const slotCard = createSlotCard(i + 1, roundData.num);
            rightGroup.appendChild(slotCard);
        }
        
        // Asamblează tabelul (fără linia de separare)
        tableContainer.appendChild(leftGroup);
        tableContainer.appendChild(rightGroup);
        
        return tableContainer;
    }

    // Creează cardul pentru finală (nu stânga-dreapta)
    function createFinalMatchCard() {
        const finalCard = document.createElement('div');
        finalCard.className = 'final-match-card';
        
        const vsContainer = document.createElement('div');
        vsContainer.className = 'final-vs-container';
        
        // Jucătorul 1
        const player1 = document.createElement('div');
        player1.className = 'final-player-slot empty';
        player1.setAttribute('data-match', 1);
        player1.setAttribute('data-player', 1);
        
        // Butonul VS
        const vsButton = document.createElement('div');
        vsButton.className = 'final-vs-button';
        vsButton.textContent = 'VS';
        vsButton.addEventListener('click', (e) => {
            e.stopPropagation();
            openMatchPopupFromBracket(1);
        });
        
        // Jucătorul 2
        const player2 = document.createElement('div');
        player2.className = 'final-player-slot empty';
        player2.setAttribute('data-match', 1);
        player2.setAttribute('data-player', 2);
        
        vsContainer.appendChild(player1);
        vsContainer.appendChild(vsButton);
        vsContainer.appendChild(player2);
        
        finalCard.appendChild(vsContainer);
        
        return finalCard;
    }

    // Creează cardul pentru un slot de jucător - folosește designul actual de la bătălie
    function createSlotCard(matchNumber, roundNum) {
        const matchCard = document.createElement('div');
        matchCard.className = `match-card round-${roundNum}`;
        matchCard.setAttribute('data-match', matchNumber);
        matchCard.setAttribute('data-round', roundNum);
        
        // Adaugă numele rundei pentru styling
        matchCard.classList.add(`round-${roundNum}`);
        
        matchCard.innerHTML = `
            <div class="match-status pending">În așteptare</div>
            <div class="match-header">Meci #${matchNumber}</div>
            <div class="match-players">
                <div class="match-player empty">
                    <div class="match-player-avatar empty-avatar"></div>
                    <div class="match-player-name">Slot liber</div>
                    <div class="match-player-score">-</div>
                </div>
                <div class="match-vs">VS</div>
                <div class="match-player empty">
                    <div class="match-player-avatar empty-avatar"></div>
                    <div class="match-player-name">Slot liber</div>
                    <div class="match-player-score">-</div>
                </div>
            </div>
        `;
        
        // Click pe card -> deschide popup
        matchCard.addEventListener('click', (e) => {
            if (!e.target.closest('.match-open-popup-btn')) {
                openMatchPopupFromBracket(matchNumber);
            }
        });
        
        return matchCard;
    }

    // Creează bracket-ul vizual pentru o rundă specifică
    function createVisualBracketForRound(round) {
        const bracketContainer = document.createElement('div');
        bracketContainer.className = 'visual-bracket-container';
        
        // Titlul rundei
        const title = document.createElement('div');
        title.className = 'visual-round-title';
        title.textContent = round.title;
        bracketContainer.appendChild(title);
        
        // Împărțim bracket-ul în două tabere (stânga și dreapta)
        const leftSide = document.createElement('div');
        leftSide.className = 'bracket-side left-side';
        
        const rightSide = document.createElement('div');
        rightSide.className = 'bracket-side right-side';
        
        // Calculăm câte meciuri pe fiecare parte
        const totalMatches = round.matches;
        const matchesPerSide = Math.ceil(totalMatches / 2);
        
        // Generează meciurile pentru partea stângă
        for (let i = 0; i < matchesPerSide; i++) {
            const matchPair = createVisualMatchPair(i + 1, round.num, totalMatches);
            leftSide.appendChild(matchPair);
        }
        
        // Generează meciurile pentru partea dreaptă
        for (let i = matchesPerSide; i < totalMatches; i++) {
            const matchPair = createVisualMatchPair(i + 1, round.num, totalMatches);
            rightSide.appendChild(matchPair);
        }
        
        bracketContainer.appendChild(leftSide);
        bracketContainer.appendChild(rightSide);
        
        return bracketContainer;
    }

    // Creează o pereche de meciuri vizuale cu sloturi circulare și VS
    function createVisualMatchPair(matchNumber, roundNum, totalMatches) {
        const matchPair = document.createElement('div');
        matchPair.className = 'visual-match-pair';
        
        // Slot pentru jucătorul 1 (circular)
        const player1Slot = document.createElement('div');
        player1Slot.className = 'visual-bracket-slot empty';
        player1Slot.setAttribute('data-match', matchNumber);
        player1Slot.setAttribute('data-player', 1);
        player1Slot.setAttribute('data-round', roundNum);
        
        // Buton VS cu notificare
        const vsButton = document.createElement('div');
        vsButton.className = 'visual-vs-button has-notification';
        vsButton.textContent = 'VS';
        vsButton.setAttribute('data-match', matchNumber);
        vsButton.addEventListener('click', (e) => {
            e.stopPropagation();
            openMatchPopupFromBracket(matchNumber);
        });
        
        // Slot pentru jucătorul 2 (circular)
        const player2Slot = document.createElement('div');
        player2Slot.className = 'visual-bracket-slot empty';
        player2Slot.setAttribute('data-match', matchNumber);
        player2Slot.setAttribute('data-player', 2);
        player2Slot.setAttribute('data-round', roundNum);
        
        // Buton chat live
        const chatButton = document.createElement('div');
        chatButton.className = 'visual-chat-live-btn has-notification';
        chatButton.innerHTML = '💬';
        chatButton.setAttribute('data-match', matchNumber);
        chatButton.addEventListener('click', (e) => {
            e.stopPropagation();
            openMatchPopupFromBracket(matchNumber);
        });
        
        // Adaugă butonul chat pe butonul VS
        vsButton.appendChild(chatButton);
        
        // Asamblează perechea
        matchPair.appendChild(player1Slot);
        matchPair.appendChild(vsButton);
        matchPair.appendChild(player2Slot);
        
        return matchPair;
    }

    // Creează o rundă pentru bracket
    function createBracketRound(round) {
        const roundDiv = document.createElement('div');
        roundDiv.className = `bracket-round round-${round.matches}`;
        
        const title = document.createElement('div');
        title.className = 'bracket-round-title';
        title.textContent = round.title;
        roundDiv.appendChild(title);
        
        // Generează meciurile pentru această rundă
        for (let i = 0; i < round.matches; i++) {
            const matchPair = createMatchPair(i + 1, round.num, round.matches);
            roundDiv.appendChild(matchPair);
        }
        
        return roundDiv;
    }

    // Creează o pereche de meciuri cu VS button și chat
    function createMatchPair(matchNumber, roundNum, totalMatches) {
        const matchPair = document.createElement('div');
        matchPair.className = 'match-pair';
        
        // Slot pentru jucătorul 1 (circular)
        const player1Slot = document.createElement('div');
        player1Slot.className = 'bracket-slot empty';
        player1Slot.setAttribute('data-match', matchNumber);
        player1Slot.setAttribute('data-player', 1);
        player1Slot.setAttribute('data-round', roundNum);
        
        // Buton VS cu notificare
        const vsButton = document.createElement('div');
        vsButton.className = 'vs-button has-notification';
        vsButton.textContent = 'VS';
        vsButton.setAttribute('data-match', matchNumber);
        vsButton.addEventListener('click', (e) => {
            e.stopPropagation();
            openMatchPopupFromBracket(matchNumber);
        });
        
        // Slot pentru jucătorul 2 (circular)
        const player2Slot = document.createElement('div');
        player2Slot.className = 'bracket-slot empty';
        player2Slot.setAttribute('data-match', matchNumber);
        player2Slot.setAttribute('data-player', 2);
        player2Slot.setAttribute('data-round', roundNum);
        
        // Buton chat live
        const chatButton = document.createElement('div');
        chatButton.className = 'chat-live-btn has-notification';
        chatButton.innerHTML = '💬';
        chatButton.setAttribute('data-match', matchNumber);
        chatButton.addEventListener('click', (e) => {
            e.stopPropagation();
            openMatchPopupFromBracket(matchNumber);
        });
        
        // Adaugă butonul chat pe butonul VS
        vsButton.appendChild(chatButton);
        
        // Asamblează perechea
        matchPair.appendChild(player1Slot);
        matchPair.appendChild(vsButton);
        matchPair.appendChild(player2Slot);
        
        return matchPair;
    }

    // Adaugă liniile de conexiune pentru bracket-ul complet
    function addCompleteBracketConnections(bracket, rounds) {
        const connectionsDiv = document.createElement('div');
        connectionsDiv.className = 'bracket-connections';
        
        // Pentru fiecare rundă (exceptând ultima)
        for (let roundIndex = 0; roundIndex < rounds.length - 1; roundIndex++) {
            const currentRound = rounds[roundIndex];
            const nextRound = rounds[roundIndex + 1];
            
            // Pentru fiecare meci din runda curentă
            for (let matchIndex = 0; matchIndex < currentRound.matches; matchIndex++) {
                // Calculează poziția meciului în runda curentă
                const currentMatchPosition = calculateMatchPosition(matchIndex, currentRound.matches);
                
                // Calculează poziția meciului în următoarea rundă
                const nextMatchIndex = Math.floor(matchIndex / 2);
                const nextMatchPosition = calculateMatchPosition(nextMatchIndex, nextRound.matches);
                
                // Creează linia de conexiune
                const connectionLine = createBracketConnectionLine(
                    roundIndex, 
                    currentMatchPosition, 
                    roundIndex + 1, 
                    nextMatchPosition
                );
                
                if (connectionLine) {
                    connectionsDiv.appendChild(connectionLine);
                }
            }
        }
        
        bracket.appendChild(connectionsDiv);
    }

    // Calculează poziția unui meci în rundă
    function calculateMatchPosition(matchIndex, totalMatches) {
        const spacing = 50; // Spacing între meciuri
        const startOffset = 50; // Offset de la începutul rundei
        
        return startOffset + matchIndex * spacing;
    }

    // Creează o linie de conexiune între două meciuri
    function createBracketConnectionLine(fromRound, fromPosition, toRound, toPosition) {
        const line = document.createElement('div');
        line.className = 'connection-line horizontal';
        
        // Calculează pozițiile relative
        const fromX = fromRound * 120 + 60; // 120px per rundă + offset
        const toX = toRound * 120 + 60;
        
        line.style.left = `${fromX}px`;
        line.style.top = `${fromPosition}px`;
        line.style.width = `${toX - fromX}px`;
        
        return line;
    }

    // Generează scheletul gol pentru o rundă specifică
    function renderEmptyRound(roundNum, container, matchCount = null) {
        // Afișează tabelul de turnament pentru runda specifică
        renderTournamentTable(container, roundNum);
    }

    // Calculează numărul de meciuri pentru o rundă
    function getMatchesForRound(roundNum) {
        const matchCounts = [32, 16, 8, 4, 1]; // 1/32, 1/16, 1/8, 1/4 (Semifinală), Finală
        return matchCounts[roundNum - 1] || 1;
    }

    // Creează un card gol pentru meci
    function createEmptyMatchCard(roundNum, matchIndex) {
        const card = document.createElement('div');
        card.className = 'match-card empty-match';
        
        card.innerHTML = `
            <div class="match-status pending">În așteptare</div>
            <div class="match-header">Meci #${matchIndex}</div>
            <div class="match-players">
                <div class="match-player empty">
                    <div class="match-player-avatar empty-avatar"></div>
                    <div class="match-player-name">Slot liber</div>
                    <div class="match-player-score">-</div>
                </div>
                <div class="match-vs">VS</div>
                <div class="match-player empty">
                    <div class="match-player-avatar empty-avatar"></div>
                    <div class="match-player-name">Slot liber</div>
                    <div class="match-player-score">-</div>
                </div>
            </div>
        `;

        return card;
    }

    function createMatchCard(match) {
        const card = document.createElement('div');
        card.className = 'match-card';
        card.setAttribute('data-match-id', match.id);
        
        if (match.completed) {
            card.classList.add('completed');
        } else if (match.round_number === currentRound) {
            card.classList.add('active');
        }

        const user1Name = match.user1_name || '???';
        const user2Name = match.user2_name || '???';
        const user1Photo = match.user1_photo || getDefaultAvatar();
        const user2Photo = match.user2_photo || getDefaultAvatar();
        const user1Vip = match.user1_vip == 1;
        const user2Vip = match.user2_vip == 1;

        const isUser1Winner = match.winner_id == match.user1_id;
        const isUser2Winner = match.winner_id == match.user2_id;

        // Verificăm dacă userul participă la acest meci
        const isUserMatch = (match.user1_id == window.userId || match.user2_id == window.userId);

        const statusBadge = match.completed 
            ? '<div class="match-status">Finalizat</div>'
            : (match.round_number === currentRound 
                ? '<div class="match-status active">LIVE</div>'
                : '<div class="match-status pending">În așteptare</div>');

        card.innerHTML = `
            ${statusBadge}
            <div class="match-header">Meci #${match.id}</div>
            <div class="match-players">
                <div class="match-player ${isUser1Winner ? 'winner' : ''} ${!match.user1_id ? 'empty' : ''}">
                    <img src="${user1Photo}" class="match-player-avatar" alt="">
                    <div class="match-player-name ${user1Vip ? 'gold-shimmer' : ''}">${user1Name}</div>
                    <div class="match-player-score">${match.user1_score || 0}</div>
                </div>
                <div class="match-vs">VS</div>
                <div class="match-player ${isUser2Winner ? 'winner' : ''} ${!match.user2_id ? 'empty' : ''}">
                    <img src="${user2Photo}" class="match-player-avatar" alt="">
                    <div class="match-player-name ${user2Vip ? 'gold-shimmer' : ''}">${user2Name}</div>
                    <div class="match-player-score">${match.user2_score || 0}</div>
                </div>
            </div>
            ${isUserMatch && !match.completed ? '<button class="match-open-popup-btn" onclick="openMatchPopupManual(event, ' + match.id + ')"><i class="fas fa-comments"></i> Vezi VS & Chat</button>' : ''}
        `;

        // Click pe card -> arată detalii (doar dacă nu e pe buton)
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.match-open-popup-btn')) {
                showMatchDetails(match.id);
            }
        });

        return card;
    }

    // Deschide popup manual din buton
    window.openMatchPopupManual = function(event, matchId) {
        event.stopPropagation();
        
        fetch(`pvp_api.php?action=get_match_details&match_id=${matchId}`)
            .then(r => r.json())
            .then(data => {
                if (data.match) {
                    // Trigger event pentru popup
                    const evt = new CustomEvent('pvp:showMatchPopup', { detail: data.match });
                    window.dispatchEvent(evt);
                }
            })
            .catch(err => console.error('Error:', err));
    };

    // Deschide popup din bracket-ul vizual
    function openMatchPopupFromBracket(matchId) {
        fetch(`pvp_api.php?action=get_match_details&match_id=${matchId}`)
            .then(r => r.json())
            .then(data => {
                if (data.match) {
                    // Trigger event pentru popup
                    const evt = new CustomEvent('pvp:showMatchPopup', { detail: data.match });
                    window.dispatchEvent(evt);
                } else {
                    // Dacă nu există match, deschide popup-ul gol pentru test
                    const mockMatch = {
                        id: matchId,
                        user1_name: 'Player 1',
                        user2_name: 'Player 2',
                        user1_score: 0,
                        user2_score: 0,
                        completed: false,
                        round_number: 1
                    };
                    const evt = new CustomEvent('pvp:showMatchPopup', { detail: mockMatch });
                    window.dispatchEvent(evt);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                // Fallback pentru test
                const mockMatch = {
                    id: matchId,
                    user1_name: 'Player 1',
                    user2_name: 'Player 2',
                    user1_score: 0,
                    user2_score: 0,
                    completed: false,
                    round_number: 1
                };
                const evt = new CustomEvent('pvp:showMatchPopup', { detail: mockMatch });
                window.dispatchEvent(evt);
            });
    }

    function showMatchDetails(matchId) {
        fetch(`pvp_api.php?action=get_match_details&match_id=${matchId}`)
            .then(r => r.json())
            .then(data => {
                if (data.match) {
                    openMatchPopup(data.match);
                }
            })
            .catch(err => {
                console.error('Error loading match details:', err);
            });
    }

    function openMatchPopup(match) {
        // Aceasta va fi gestionată de pvp-popup.js
        // Trimitem event custom
        const event = new CustomEvent('pvp:showMatchPopup', { detail: match });
        window.dispatchEvent(event);
    }

    // Event listeners pentru tabs ligi
    const tabButtons = document.querySelectorAll('.pvp-tabs .tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.dataset.tab;
            const leagueId = parseInt(btn.dataset.leagueId);

            // Update active state
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Show/hide content
            document.querySelectorAll('.pvp-tab-content .tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(targetTab).classList.add('active');

            currentLeagueId = leagueId;
            
            // Reset round selection to first round (1/32)
            const roundTabs = document.querySelectorAll(`#roundTabs-${leagueId} .round-btn`);
            roundTabs.forEach(r => r.classList.remove('active'));
            if (roundTabs.length > 0) {
                roundTabs[0].classList.add('active');
            }
            
            // Show tournament table for first round
            const loadingEl = document.getElementById(`loading-${leagueId}`);
            const bracketEl = document.getElementById(`bracket-${leagueId}`);
            
            // Ascunde loading-ul imediat
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
            
            if (bracketEl) {
                bracketEl.style.display = 'block';
                renderTournamentTable(bracketEl, 1); // Start with 1/32
            }
        });
    });

    // Event listeners pentru round tabs
    document.querySelectorAll('.pvp-round-tabs').forEach(container => {
        const leagueId = parseInt(container.id.replace('roundTabs-', ''));
        
        container.querySelectorAll('.round-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const roundNum = parseInt(btn.dataset.round);

                // Update active state
                container.querySelectorAll('.round-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Show tournament table for specific round
                const bracketEl = document.getElementById(`bracket-${leagueId}`);
                if (bracketEl) {
                    bracketEl.style.display = 'block';
                    renderTournamentTable(bracketEl, roundNum);
                }
            });
        });
    });

    // Inițializare
    updateBattleStatus();
    
    // Show tournament table for user's league
    const loadingEl = document.getElementById(`loading-${userLeagueId}`);
    const bracketEl = document.getElementById(`bracket-${userLeagueId}`);
    
    // Ascunde loading-ul imediat
    if (loadingEl) {
        loadingEl.style.display = 'none';
    }
    
    if (bracketEl) {
        bracketEl.style.display = 'block';
        renderTournamentTable(bracketEl, 1); // Start with 1/32
    }

    // Update periodic (la 10 secunde)
    updateInterval = setInterval(() => {
        updateBattleStatus();
        
        // Refresh bracket-ul curent cu sistemul vizual
            const activeTab = document.querySelector('.pvp-tab-content .tab-content.active');
            if (activeTab) {
                const leagueId = parseInt(activeTab.dataset.leagueId);
                const activeRound = document.querySelector(`#roundTabs-${leagueId} .round-btn.active`);
            const roundNum = activeRound ? parseInt(activeRound.dataset.round) : 1;
            const bracketEl = document.getElementById(`bracket-${leagueId}`);
            if (bracketEl) {
                renderTournamentTable(bracketEl, roundNum);
            }
        }
    }, 10000);

    // Cleanup la părăsirea paginii
    window.addEventListener('beforeunload', () => {
        if (updateInterval) {
            clearInterval(updateInterval);
        }
    });

    // ===== BADGE-URI PENTRU CHAT =====

    // Listener pentru update badge-uri din pvp-chat.js
    window.addEventListener('pvp:updateChatBadge', (event) => {
        const { count, matchId } = event.detail;
        
        if (!matchId) return;

        // Badge pe tab ligă
        updateLeagueTabBadge(count);
        
        // Badge pe tab rundă
        updateRoundTabBadge(count, matchId);
    });

    function updateLeagueTabBadge(count) {
        const activeLeagueTab = document.querySelector('.pvp-tabs .tab-btn.active');
        if (!activeLeagueTab) return;

        let badge = activeLeagueTab.querySelector('.pvp-tab-badge');
        
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'pvp-tab-badge';
                activeLeagueTab.style.position = 'relative';
                activeLeagueTab.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }

    function updateRoundTabBadge(count, matchId) {
        // Găsim runda curentă
        const activeRoundTab = document.querySelector('.pvp-round-tabs .round-btn.active');
        if (!activeRoundTab) return;

        let badge = activeRoundTab.querySelector('.pvp-round-badge');
        
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'pvp-round-badge';
                activeRoundTab.style.position = 'relative';
                activeRoundTab.appendChild(badge);
            }
            badge.textContent = count;
        } else if (badge) {
            badge.remove();
        }
    }
});

