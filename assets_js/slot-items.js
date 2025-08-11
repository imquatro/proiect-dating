// Handles planted item overlays, watering/feeding timers, and harvest prompts

document.addEventListener('DOMContentLoaded', () => {
    const slotStates = {};

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

        if (state.waterRemaining > 0) {
            actionEl.textContent = 'WATER';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
        } else if (state.feedRemaining > 0) {
            actionEl.textContent = 'FEED';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
        } else {
            actionEl.textContent = 'HARVEST';
            actionEl.classList.add('harvest');
            actionEl.style.display = 'flex';
        }
    }

    function startTimer(slotId, type) {
        const state = slotStates[slotId];
        const slot = document.getElementById(`slot-${slotId}`);
        if (!state || !slot) return;
        const timerEl = slot.querySelector('.slot-timer');
        const actionEl = slot.querySelector('.slot-action');

        state.timerType = type;
        state.timeLeft = type === 'water' ? state.waterInterval : state.feedInterval;
        if (timerEl) {
            timerEl.style.display = 'block';
        }
        if (actionEl) {
            actionEl.style.display = 'none';
        }
        updateTimer(slotId);

        state.timer = setInterval(() => {
            state.timeLeft--;
            if (state.timeLeft <= 0) {
                clearInterval(state.timer);
                state.timer = null;
                if (timerEl) timerEl.style.display = 'none';
                checkNextAction(slotId);
            } else {
                updateTimer(slotId);
            }
        }, 1000);
    }

    function handleActionClick(e) {
        e.stopPropagation();
        const slot = e.currentTarget.closest('.farm-slot');
        const slotId = slot.id.replace('slot-', '');
        const action = e.currentTarget.textContent;
        const state = slotStates[slotId];
        if (!state) return;

        if (action === 'WATER') {
            if (state.waterRemaining > 0) {
                state.waterRemaining--;
                if (state.waterInterval > 0) {
                    startTimer(slotId, 'water');
                } else {
                    checkNextAction(slotId);
                }
            }
        } else if (action === 'FEED') {
            if (state.feedRemaining > 0) {
                state.feedRemaining--;
                if (state.feedInterval > 0) {
                    startTimer(slotId, 'feed');
                } else {
                    checkNextAction(slotId);
                }
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
            waterInterval: parseInt(waterInterval) || 0,
            feedInterval: parseInt(feedInterval) || 0,
            waterRemaining: parseInt(waterTimes) || 0,
            feedRemaining: parseInt(feedTimes) || 0,
            timer: null,
            timerType: null,
            timeLeft: 0
        };
        checkNextAction(slotId);
    });
});