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
    
    // Broadcast Channel for instant sync between tabs on same device
    let broadcastChannel = null;
    try {
        const channelName = isVisitor && visitId ? `farm_${visitId}` : `farm_${window.userId || 'guest'}`;
        broadcastChannel = new BroadcastChannel(channelName);
        broadcastChannel.onmessage = (e) => {
            if (e.data && e.data.type === 'slot_update') {
                updateSlotFromStream(e.data.data);
            }
        };
    } catch (e) {
        // BroadcastChannel not supported, ignore
    }

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
        // Use sendBeacon only when saving all states (e.g., on unload)
        if (slotId === null && navigator.sendBeacon) {
            const blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(fetchUrl, blob);
            dirtySlots.clear();
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
        const owner = (isVisitor && visitId) ? visitId : (window.userId || null);
        if (!owner) return Promise.resolve({});
        const url = (window.baseUrl || '') + 'record_help.php';
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `owner_id=${owner}&slot_id=${slotId}&action=${encodeURIComponent(action)}`
        }).then(res => res.json()).then(data => {
            if (data.levelUp && window.showLevelUp) {
                window.showLevelUp(data.newLevel);
            }
            return data;
        }).catch(() => ({}));
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

        // Timer visibility will be handled elsewhere when states change

        if (state.waterRemaining > 0) {
            actionEl.dataset.action = 'water';
            actionEl.innerHTML = '<span class="action-icon water-icon" aria-label="Water">💧</span>';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
            actionEl.style.pointerEvents = 'auto';
        } else if (state.feedRemaining > 0) {
            actionEl.dataset.action = 'feed';
            actionEl.innerHTML = '<span class="action-icon feed-icon" aria-label="Feed">🍖</span>';
            actionEl.classList.remove('harvest');
            actionEl.style.display = 'flex';
            actionEl.style.pointerEvents = 'auto';
        } else {
            actionEl.dataset.action = 'harvest';
            actionEl.innerHTML = '<span class="action-icon harvest-icon" aria-label="Harvest">❕</span>';
            actionEl.classList.add('harvest');
            actionEl.style.display = 'flex';
            if (isVisitor) {
                actionEl.style.pointerEvents = 'none';
            } else {
                actionEl.style.pointerEvents = 'auto';
            }
            state.timerType = 'harvest';
            state.timerEnd = null;
        }
    }

    function bindActionHandler(actionEl) {
        if (!actionEl) return;
        if (actionEl.dataset.bound) return;
        actionEl.addEventListener('click', handleActionClick);
        actionEl.dataset.bound = 'true';
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
        
        // Check all slots for harvest-ready state (even those without active timers)
        Object.keys(slotStates).forEach(id => {
            const state = slotStates[id];
            if (state && state.image && state.waterRemaining === 0 && state.feedRemaining === 0) {
                const slot = document.getElementById(`slot-${id}`);
                if (slot) {
                    const actionEl = slot.querySelector('.slot-action');
                    if (actionEl && actionEl.dataset.action !== 'harvest') {
                        checkNextAction(id);
                    }
                }
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

        if (isVisitor && !canInteract) {
            if (window.showFriendRequestCard) window.showFriendRequestCard();
            return;
        }
        const state = slotStates[slotId];
        if (!state) return;

        if (action === 'water') {
            if (state.waterRemaining > 0) {
                // Server-side watering with optimistic UI update
                const ownerId = (isVisitor && visitId) ? visitId : (window.userId || null);
                if (!ownerId) return;
                
                // Optimistic update
                const prevWater = state.waterRemaining;
                state.waterRemaining--;
                if (state.waterRemaining > 0 && state.waterInterval > 0) {
                    startTimer(slotId, 'water');
                } else {
                    checkNextAction(slotId);
                }
                
                // Broadcast instant update to other tabs on same device
                if (broadcastChannel) {
                    try {
                        broadcastChannel.postMessage({
                            type: 'slot_update',
                            data: {
                                slotId: parseInt(slotId),
                                waterTimes: state.waterRemaining,
                                feedTimes: state.feedRemaining,
                                timerType: state.timerType,
                                timerEnd: state.timerEnd,
                                image: state.image,
                                waterInterval: state.waterInterval,
                                feedInterval: state.feedInterval
                            }
                        });
                    } catch (e) {
                        // Ignore broadcast errors
                    }
                }
                
                fetch(`${(window.baseUrl || '')}serverside/water_slot.php`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({slot_id: parseInt(slotId), owner_id: parseInt(ownerId)})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Apply server response immediately
                        state.waterRemaining = data.waterRemaining;
                        state.feedRemaining = data.feedRemaining;
                        state.timerType = data.timerType;
                        state.timerEnd = data.timerEnd;
                        
                        if (data.timerEnd && data.timerEnd > Date.now()) {
                            startTimer(slotId, data.timerType, true);
                        } else {
                            checkNextAction(slotId);
                        }
                        
                        // Force immediate state reload for all slots to catch harvest-ready
                        loadStates().catch(() => {});
                        
                        if (window.showFloatingText && data.xpGain) {
                            window.showFloatingText(slot, {xp: data.xpGain});
                        }
                        if (data.levelUp && window.showLevelUp) {
                            window.showLevelUp(data.newLevel);
                        }
                    } else {
                        state.waterRemaining = prevWater;
                        checkNextAction(slotId);
                    }
                })
                .catch(() => {
                    state.waterRemaining = prevWater;
                    checkNextAction(slotId);
                });
            }
        } else if (action === 'feed') {
            if (state.feedRemaining > 0) {
                // Server-side feeding with optimistic UI update
                const ownerId = (isVisitor && visitId) ? visitId : (window.userId || null);
                if (!ownerId) return;
                
                // Optimistic update
                const prevFeed = state.feedRemaining;
                state.feedRemaining--;
                if (state.feedRemaining > 0 && state.feedInterval > 0) {
                    startTimer(slotId, 'feed');
                } else {
                    checkNextAction(slotId);
                }
                
                // Broadcast instant update to other tabs on same device
                if (broadcastChannel) {
                    try {
                        broadcastChannel.postMessage({
                            type: 'slot_update',
                            data: {
                                slotId: parseInt(slotId),
                                waterTimes: state.waterRemaining,
                                feedTimes: state.feedRemaining,
                                timerType: state.timerType,
                                timerEnd: state.timerEnd,
                                image: state.image,
                                waterInterval: state.waterInterval,
                                feedInterval: state.feedInterval
                            }
                        });
                    } catch (e) {
                        // Ignore broadcast errors
                    }
                }
                
                fetch(`${(window.baseUrl || '')}serverside/feed_slot.php`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({slot_id: parseInt(slotId), owner_id: parseInt(ownerId)})
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Apply server response immediately
                        state.waterRemaining = data.waterRemaining;
                        state.feedRemaining = data.feedRemaining;
                        state.timerType = data.timerType;
                        state.timerEnd = data.timerEnd;
                        
                        if (data.timerEnd && data.timerEnd > Date.now()) {
                            startTimer(slotId, data.timerType, true);
                        } else {
                            checkNextAction(slotId);
                        }
                        
                        // Force immediate state reload for all slots to catch harvest-ready
                        loadStates().catch(() => {});
                        
                        if (window.showFloatingText && data.xpGain) {
                            window.showFloatingText(slot, {xp: data.xpGain});
                        }
                        if (data.levelUp && window.showLevelUp) {
                            window.showLevelUp(data.newLevel);
                        }
                    } else {
                        state.feedRemaining = prevFeed;
                        checkNextAction(slotId);
                    }
                })
                .catch(() => {
                    state.feedRemaining = prevFeed;
                    checkNextAction(slotId);
                });
            }
        } else if (action === 'harvest') {
            // No direct harvest here; the slot's click handler will
            // open the panel where the user can confirm harvesting.
            return;
        }
    }
    document.addEventListener('slotUpdated', e => {
        const {
            slotId,
            image,
            waterInterval,
            feedInterval,
            waterTimes,
            feedTimes,
            timerType,
            timerEnd,
            type
        } = e.detail || {};
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
        if (actionEl) { bindActionHandler(actionEl); }

        const existing = slotStates[slotId] || {};
        const merged = {
            image,
            waterInterval: parseInt(waterInterval) || existing.waterInterval || 0,
            feedInterval: parseInt(feedInterval) || existing.feedInterval || 0,
            waterRemaining: parseInt(waterTimes) || existing.waterRemaining || 0,
            feedRemaining: parseInt(feedTimes) || existing.feedRemaining || 0,
            timerType: timerType || existing.timerType || null,
            timerEnd: timerEnd || existing.timerEnd || null,
            timeLeft: existing.timeLeft || 0
        };
        slotStates[slotId] = merged;

        const timerEl = slot.querySelector('.slot-timer');
        if (merged.timerEnd && merged.timerEnd > Date.now()) {
            merged.timeLeft = Math.round((merged.timerEnd - Date.now()) / 1000);
            if (timerEl) timerEl.style.display = 'block';
            if (actionEl) actionEl.style.display = 'none';
            updateTimer(slotId);
            activeTimers.add(slotId);
        } else {
            merged.timerEnd = null;
            merged.timeLeft = 0;
            if (timerEl) timerEl.style.display = 'none';
            checkNextAction(slotId);
        }
        saveStates(slotId);
    });

    async function loadStates() {
        // Bypass any HTTP caching to ensure we always have up-to-date
        // slot information after interactions like watering/feeding.
        const res = await fetch(fetchUrl, { cache: 'no-store' });
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

            const existing = slotStates[id] || {};
            slotStates[id] = {
                image: incoming.image,
                waterInterval: parseInt(incoming.waterInterval) || existing.waterInterval || 0,
                feedInterval: parseInt(incoming.feedInterval) || existing.feedInterval || 0,
                waterRemaining: parseInt(incoming.waterRemaining) || existing.waterRemaining || 0,
                feedRemaining: parseInt(incoming.feedRemaining) || existing.feedRemaining || 0,
                timerType: incoming.timerType || existing.timerType || null,
                timerEnd: incoming.timerEnd || existing.timerEnd || null,
                timeLeft: existing.timeLeft || 0
            };

            if (itemImg && incoming.image) {
                itemImg.src = incoming.image;
                itemImg.style.display = 'block';
            }
            if (actionEl) {
                bindActionHandler(actionEl);
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
                if (timerEl) timerEl.style.display = 'none';
                activeTimers.delete(id);
                checkNextAction(id);
            }
        });
    }

    // Function to update slot from SSE stream
    function updateSlotFromStream(data) {
        const slotId = String(data.slotId);
        if (dirtySlots.has(slotId)) return; // Skip if we just modified it
        
        const slot = document.getElementById(`slot-${slotId}`);
        if (!slot) return;
        
        const itemImg = slot.querySelector('.slot-item');
        const actionEl = slot.querySelector('.slot-action');
        const timerEl = slot.querySelector('.slot-timer');
        
        const existing = slotStates[slotId] || {};
        slotStates[slotId] = {
            image: data.image || existing.image,
            waterInterval: parseInt(data.waterInterval) || existing.waterInterval || 0,
            feedInterval: parseInt(data.feedInterval) || existing.feedInterval || 0,
            waterRemaining: parseInt(data.waterTimes) !== undefined ? parseInt(data.waterTimes) : existing.waterRemaining || 0,
            feedRemaining: parseInt(data.feedTimes) !== undefined ? parseInt(data.feedTimes) : existing.feedRemaining || 0,
            timerType: data.timerType !== undefined ? data.timerType : existing.timerType,
            timerEnd: data.timerEnd !== undefined ? data.timerEnd : existing.timerEnd,
            timeLeft: existing.timeLeft || 0
        };
        
        if (itemImg && data.image) {
            itemImg.src = data.image;
            itemImg.style.display = 'block';
        }
        
        if (actionEl) {
            bindActionHandler(actionEl);
        }
        
        if (slotStates[slotId].timerEnd && slotStates[slotId].timerEnd > Date.now()) {
            slotStates[slotId].timeLeft = Math.round((slotStates[slotId].timerEnd - Date.now()) / 1000);
            if (timerEl) timerEl.style.display = 'block';
            if (actionEl) actionEl.style.display = 'none';
            updateTimer(slotId);
            activeTimers.add(slotId);
        } else {
            slotStates[slotId].timerEnd = null;
            slotStates[slotId].timeLeft = 0;
            if (timerEl) timerEl.style.display = 'none';
            activeTimers.delete(slotId);
            checkNextAction(slotId);
        }
    }

    // Initial load
    loadStates();
    
    // Real-time updates via Server-Sent Events (EventSource)
    const streamUrl = isVisitor && visitId 
        ? `${(window.baseUrl || '')}slot_stream.php?user_id=${visitId}`
        : `${(window.baseUrl || '')}slot_stream.php`;
    
    let eventSource = null;
    let reconnectTimeout = null;
    
    function connectEventSource() {
        if (eventSource) {
            eventSource.close();
        }
        
        eventSource = new EventSource(streamUrl);
        
        eventSource.onmessage = (e) => {
            try {
                const data = JSON.parse(e.data);
                updateSlotFromStream(data);
            } catch (err) {
                console.error('SSE parse error:', err);
            }
        };
        
        eventSource.onerror = () => {
            eventSource.close();
            // Reconnect after 3 seconds
            if (!reconnectTimeout) {
                reconnectTimeout = setTimeout(() => {
                    reconnectTimeout = null;
                    connectEventSource();
                }, 3000);
            }
        };
    }
    
    connectEventSource();
    
    // Aggressive fallback polling every 3 seconds for near-instant updates
    setInterval(loadStates, 3000);
    
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (eventSource) {
                eventSource.close();
            }
        } else {
            loadStates();
            connectEventSource();
        }
    });
    
    window.addEventListener('beforeunload', () => {
        if (eventSource) {
            eventSource.close();
        }
        if (broadcastChannel) {
            broadcastChannel.close();
        }
        saveStates();
    });
});

