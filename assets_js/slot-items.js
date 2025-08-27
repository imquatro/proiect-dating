// Handles planted item overlays, watering/feeding timers, and harvest prompts

document.addEventListener('DOMContentLoaded', () => {
    // Persist slot states on the server so they survive logout and long pauses
    const slotStates = {};
    const activeTimers = new Set();
    const dirtySlots   = new Set();
    const isVisitor = window.isVisitor || false;
    const visitId = window.visitId || null;
    const canInteract = window.canInteract || false;
    const fetchUrl = isVisitor && visitId
        ? `${(window.baseUrl || '')}slot_states.php?user_id=${visitId}`
        : (window.baseUrl || '') + 'slot_states.php';

    function saveStates(slotId = null) {
        if (isVisitor && !canInteract) return Promise.resolve();
        let payload;
        if (slotId !== null) {
            payload = {};
            payload[slotId] = slotStates[slotId] || null;
            dirtySlots.add(String(slotId));
        } else {
            payload = slotStates;
            Object.keys(slotStates).forEach(id => dirtySlots.add(String(id)));
        }
        const body = JSON.stringify(payload);
        if (navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(fetchUrl, blob);
            if (slotId !== null) dirtySlots.delete(String(slotId));
            else dirtySlots.clear();
            return Promise.resolve();
        }
        return fetch(fetchUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body,
            keepalive: true
        }).finally(() => {
            if (slotId !== null) dirtySlots.delete(String(slotId));
            else dirtySlots.clear();
        });
    }

    function recordHelp(slotId, action) {
        if (!isVisitor || !visitId) return;
        const url = (window.baseUrl || '') + 'record_help.php';
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `owner_id=${visitId}&slot_id=${slotId}&action=${encodeURIComponent(action)}`
        });
    }

    function formatTime(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    function updateTimer(slotId) {
        const state = slotStates[slotId];
        const timerEl = document.querySelector(`#slot-${slotId} .slot-timer`);
        if (state && timerEl) {
            timerEl.textContent = formatTime(state.timeLeft);
        }
    }

    function checkNextAction(slotId) {
        const state = slotStates[slotId];
        const slot = document.getElementById(`slot-${slotId}`);
        if (!state || !slot) return;
        const actionEl = slot.querySelector('.slot-action');
        if (!actionEl) return;
        if (!state.image) {
            actionEl.style.display = 'none';
            return;
        }

        if (state.waterRemaining > 0) {
            actionEl.dataset.action = 'water';
            actionEl.innerHTML = '<img src="img/uda.png" alt="Water">';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
        } else if (state.feedRemaining > 0) {
            actionEl.dataset.action = 'feed';
            actionEl.innerHTML = '<img src="img/hraneste.png" alt="Feed">';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
        } else {
            actionEl.dataset.action = 'harvest';
            actionEl.innerHTML = '<img src="img/ready.png" alt="Harvest">';
            actionEl.classList.add('harvest');
            actionEl.style.display = 'flex';
        }
    }

    function startTimer(slotId, type, resume = false) {
        const state = slotStates[slotId];
        const slot = document.getElementById(`slot-${slotId}`);
        if (!state || !slot) return;
        const timerEl = slot.querySelector('.slot-timer');
        const actionEl = slot.querySelector('.slot-action');

        state.timerType = type;
        if (!resume) {
            state.timeLeft = type === 'water' ? state.waterInterval : state.feedInterval;
            state.timerEnd = Date.now() + state.timeLeft * 1000;
        } else {
            state.timeLeft = Math.max(0, Math.round((state.timerEnd - Date.now()) / 1000));
        }
        if (timerEl) timerEl.style.display = 'block';
        if (actionEl) actionEl.style.display = 'none';
        updateTimer(slotId);
        activeTimers.add(slotId);
    }

    function tickTimers() {
        const now = Date.now();
        activeTimers.forEach(id => {
            const state = slotStates[id];
            if (!state || !state.timerEnd) {
                activeTimers.delete(id);
                return;
            }
            state.timeLeft = Math.max(0, Math.round((state.timerEnd - now) / 1000));
            if (state.timeLeft <= 0) {
                activeTimers.delete(id);
                state.timerEnd = null;
                state.timerType = null;
                const slot = document.getElementById(`slot-${id}`);
                const timerEl = slot ? slot.querySelector('.slot-timer') : null;
                if (timerEl) timerEl.style.display = 'none';
                requestAnimationFrame(() => checkNextAction(id));
                saveStates(id);
            } else {
                updateTimer(id);
            }
        });
    }
    setInterval(tickTimers, 1000);

    function clearSlot(slotId) {
        const slot = document.getElementById(`slot-${slotId}`);
        if (!slot) return;
        const itemImg  = slot.querySelector('.slot-item');
        const actionEl = slot.querySelector('.slot-action');
        const timerEl  = slot.querySelector('.slot-timer');
        if (itemImg)  { itemImg.style.display = 'none'; itemImg.src = ''; }
        if (actionEl) { actionEl.style.display = 'none'; actionEl.classList.remove('harvest'); }
        if (timerEl)  { timerEl.style.display = 'none'; }
        activeTimers.delete(slotId);
    }

    function handleActionClick(e) {
        const actionEl = e.currentTarget;
        const action   = actionEl.dataset.action;
        const slot     = actionEl.closest('.farm-slot');
        const slotId   = slot.id.replace('slot-', '');

        // For watering/feeding we prevent the slot click handler
        // from opening the management panel. Harvest actions should
        // fall through so the slot click handler can open the panel
        // and perform the server-side harvest via the old flow.
        if (action !== 'harvest') {
            e.stopPropagation();
        }

        if (isVisitor && !canInteract) return;
        const state = slotStates[slotId];
        if (!state) return;

        if (action === 'water') {
            if (state.waterRemaining > 0) {
                state.waterRemaining--;
                if (state.waterInterval > 0) {
                    startTimer(slotId, 'water');
                } else {
                    checkNextAction(slotId);
                }
                saveStates(slotId);
                recordHelp(slotId, 'water');
            }
        } else if (action === 'feed') {
            if (state.feedRemaining > 0) {
                state.feedRemaining--;
                if (state.feedInterval > 0) {
                    startTimer(slotId, 'feed');
                } else {
                    checkNextAction(slotId);
                }
                saveStates(slotId);
                recordHelp(slotId, 'feed');
            }
        } else if (action === 'harvest') {
            // No direct harvest here; the slot's click handler will
            // open the panel where the user can confirm harvesting.
            return;
        }
    }
    document.addEventListener('slotUpdated', e => {
        const { slotId, image, waterInterval, feedInterval, waterTimes, feedTimes, type } = e.detail || {};
        if (!slotId) return;

        // Slot type change should clear existing item state
        if (type && waterInterval === undefined && feedInterval === undefined) {
            const slot = document.getElementById(`slot-${slotId}`);
            if (slot) {
                const itemImg = slot.querySelector('.slot-item');
                const actionEl = slot.querySelector('.slot-action');
                const timerEl = slot.querySelector('.slot-timer');
                if (itemImg) { itemImg.style.display = 'none'; itemImg.src = ''; }
                if (actionEl) { actionEl.style.display = 'none'; actionEl.classList.remove('harvest'); }
                if (timerEl) { timerEl.style.display = 'none'; }
            }
            delete slotStates[slotId];
            activeTimers.delete(slotId);
            saveStates(slotId);
            return;
        }

        if (!image) return;
        const slot = document.getElementById(`slot-${slotId}`);
        if (!slot) return;
        const itemImg = slot.querySelector('.slot-item');
        if (itemImg) { itemImg.src = image; itemImg.style.display = 'block'; }
        const actionEl = slot.querySelector('.slot-action');
        if (actionEl) { actionEl.addEventListener('click', handleActionClick); }
        slotStates[slotId] = {
            image,
            waterInterval: parseInt(waterInterval) || 0,
            feedInterval: parseInt(feedInterval) || 0,
            waterRemaining: parseInt(waterTimes) || 0,
            feedRemaining: parseInt(feedTimes) || 0,
            timerType: null,
            timeLeft: 0,
            timerEnd: null
        };
        saveStates(slotId);
        checkNextAction(slotId);
    });

    async function loadStates() {
        const res = await fetch(fetchUrl);
        const data = await res.json();

        // Remove states that no longer exist on the server
        Object.keys(slotStates).forEach(id => {
            if (!data[id] && !dirtySlots.has(id)) {
                const slot = document.getElementById(`slot-${id}`);
                if (slot) {
                    const itemImg = slot.querySelector('.slot-item');
                    const actionEl = slot.querySelector('.slot-action');
                    const timerEl = slot.querySelector('.slot-timer');
                    if (itemImg) { itemImg.style.display = 'none'; itemImg.src = ''; }
                    if (actionEl) { actionEl.style.display = 'none'; actionEl.classList.remove('harvest'); }
                    if (timerEl) { timerEl.style.display = 'none'; }
                }
                activeTimers.delete(id);
                delete slotStates[id];
            }
        });

        Object.keys(data).forEach(id => {
            if (dirtySlots.has(id)) return;
            const incoming = data[id];
            const slot = document.getElementById(`slot-${id}`);
            if (!slot) return;
            const itemImg = slot.querySelector('.slot-item');
            const actionEl = slot.querySelector('.slot-action');
            const timerEl = slot.querySelector('.slot-timer');
            slotStates[id] = {
                image: incoming.image,
                waterInterval: parseInt(incoming.waterInterval) || 0,
                feedInterval: parseInt(incoming.feedInterval) || 0,
                waterRemaining: parseInt(incoming.waterRemaining) || 0,
                feedRemaining: parseInt(incoming.feedRemaining) || 0,
                timerType: incoming.timerType || null,
                timerEnd: incoming.timerEnd || null,
                timeLeft: 0
            };

            if (itemImg && incoming.image) {
                itemImg.src = incoming.image;
                itemImg.style.display = 'block';
            }
            if (actionEl) {
                actionEl.addEventListener('click', handleActionClick);
            }
            if (slotStates[id].timerEnd && slotStates[id].timerEnd > Date.now()) {
                slotStates[id].timeLeft = Math.round((slotStates[id].timerEnd - Date.now()) / 1000);
                if (timerEl) timerEl.style.display = 'block';
                if (actionEl) actionEl.style.display = 'none';
                updateTimer(id);
                activeTimers.add(id);
            } else {
                slotStates[id].timerEnd = null;
                slotStates[id].timeLeft = 0;
                checkNextAction(id);
            }
        });
    }

    // Initial load and periodic refresh for live updates
    loadStates();
    let pollInterval = setInterval(loadStates, 5000);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollInterval);
        } else {
            loadStates();
            pollInterval = setInterval(loadStates, 5000);
        }
    });
    window.addEventListener('beforeunload', () => saveStates());
});
