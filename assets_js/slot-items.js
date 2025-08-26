// Handles planted item overlays, watering/feeding timers, and harvest prompts

document.addEventListener('DOMContentLoaded', () => {
    // Persist slot states on the server so they survive logout and long pauses
    const slotStates = {};
    const isVisitor = window.isVisitor || false;
    const visitId = window.visitId || null;
    const canInteract = window.canInteract || false;
    const fetchUrl = isVisitor && visitId ? `slot_states.php?user_id=${visitId}` : 'slot_states.php';

    function saveStates() {
        if (isVisitor && !canInteract) return;
        fetch(fetchUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(slotStates)
        });
    }

    function recordHelp(action) {
        if (!isVisitor || !visitId) return;
        fetch('record_help.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `owner_id=${visitId}&action=${encodeURIComponent(action)}`
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
        }
        saveStates();
        if (timerEl) {
            timerEl.style.display = 'block';
        }
        if (actionEl) {
            actionEl.style.display = 'none';
        }
        updateTimer(slotId);

        state.timer = setInterval(() => {
            state.timeLeft = Math.max(0, Math.round((state.timerEnd - Date.now()) / 1000));
            if (state.timeLeft <= 0) {
                clearInterval(state.timer);
                state.timer = null;
                state.timerEnd = null;
                if (timerEl) timerEl.style.display = 'none';
                // Ensure the timer is hidden before showing the next action
                requestAnimationFrame(() => checkNextAction(slotId));
                saveStates();
            } else {
                updateTimer(slotId);
            }
        }, 1000);
    }

    async function handleActionClick(e) {
        const actionEl = e.currentTarget;
        const action = actionEl.dataset.action;
        if (action === 'harvest') {
            // Allow click to bubble so the slot panel can handle harvesting
            return;
        }
        e.stopPropagation();
        const slot = actionEl.closest('.farm-slot');
        const slotId = slot.id.replace('slot-', '');

        if (isVisitor && !canInteract) return;
        // Refresh states to avoid acting on stale data
        await loadStates();
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
                saveStates();
                recordHelp('water');
            } else {
                alert('This slot is already watered');
            }
        } else if (action === 'feed') {
            if (state.feedRemaining > 0) {
                state.feedRemaining--;
                if (state.feedInterval > 0) {
                    startTimer(slotId, 'feed');
                } else {
                    checkNextAction(slotId);
                }
                saveStates();
                recordHelp('feed');
            } else {
                alert('This slot is already fed');
            }
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
                if (itemImg) {
                    itemImg.style.display = 'none';
                    itemImg.src = '';
                }
                if (actionEl) {
                    actionEl.style.display = 'none';
                    actionEl.classList.remove('harvest');
                }
                if (timerEl) {
                    timerEl.style.display = 'none';
                }
            }
            delete slotStates[slotId];
            saveStates();
            return;
        }

        if (!image) return;
        const slot = document.getElementById(`slot-${slotId}`);
        if (!slot) return;
        const itemImg = slot.querySelector('.slot-item');
        if (itemImg) {
            itemImg.src = image;
            itemImg.style.display = 'block';
        }
        const actionEl = slot.querySelector('.slot-action');
        if (actionEl) {
            actionEl.addEventListener('click', handleActionClick);
        }
        slotStates[slotId] = {
            image,
            waterInterval: parseInt(waterInterval) || 0,
            feedInterval: parseInt(feedInterval) || 0,
            waterRemaining: parseInt(waterTimes) || 0,
            feedRemaining: parseInt(feedTimes) || 0,
            timer: null,
            timerType: null,
            timeLeft: 0,
            timerEnd: null
        };
        saveStates();
        checkNextAction(slotId);
    });

    async function loadStates() {
        const res = await fetch(fetchUrl);
        const data = await res.json();

        // Remove states that no longer exist on the server
        Object.keys(slotStates).forEach(id => {
            if (!data[id]) {
                const old = slotStates[id];
                if (old.timer) clearInterval(old.timer);
                const slot = document.getElementById(`slot-${id}`);
                if (slot) {
                    const itemImg = slot.querySelector('.slot-item');
                    const actionEl = slot.querySelector('.slot-action');
                    const timerEl = slot.querySelector('.slot-timer');
                    if (itemImg) { itemImg.style.display = 'none'; itemImg.src = ''; }
                    if (actionEl) { actionEl.style.display = 'none'; actionEl.classList.remove('harvest'); }
                    if (timerEl) { timerEl.style.display = 'none'; }
                }
                delete slotStates[id];
            }
        });

        Object.keys(data).forEach(id => {
            const incoming = data[id];
            const slot = document.getElementById(`slot-${id}`);
            if (!slot) return;
            const itemImg = slot.querySelector('.slot-item');
            const actionEl = slot.querySelector('.slot-action');
            const timerEl = slot.querySelector('.slot-timer');

            if (slotStates[id] && slotStates[id].timer) {
                clearInterval(slotStates[id].timer);
            }
            slotStates[id] = {
                image: incoming.image,
                waterInterval: parseInt(incoming.waterInterval) || 0,
                feedInterval: parseInt(incoming.feedInterval) || 0,
                waterRemaining: parseInt(incoming.waterRemaining) || 0,
                feedRemaining: parseInt(incoming.feedRemaining) || 0,
                timerType: incoming.timerType || null,
                timerEnd: incoming.timerEnd || null,
                timer: null,
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
                startTimer(id, slotStates[id].timerType, true);
            } else {
                slotStates[id].timer = null;
                slotStates[id].timerEnd = null;
                slotStates[id].timeLeft = 0;
                checkNextAction(id);
            }
        });
    }

    // Initial load and periodic refresh for live updates
    loadStates();
    setInterval(loadStates, 5000);
    window.addEventListener('beforeunload', saveStates);
});