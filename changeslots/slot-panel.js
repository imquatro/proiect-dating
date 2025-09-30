function initSlotPanel(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    const changeBtn = container.querySelector('#cs-slot-change');
    const shopBtn = container.querySelector('#cs-slot-shop');
    const removeBtn = container.querySelector('#cs-slot-remove');
    const removeAllBtn = container.querySelector('#cs-slot-remove-all');
    const harvestBtn = container.querySelector('#cs-slot-harvest');
    const harvestAllBtn = container.querySelector('#cs-slot-harvest-all');
    const slotImage = container.querySelector('#cs-slot-image');
    const slotId = slotImage ? slotImage.alt.replace(/\D/g, '') : '';
    const slotNum = parseInt(slotId, 10);
    const isVip = container.dataset.isVip === '1';

    function showConfirm(message, onConfirm) {
        const overlay = document.createElement('div');
        overlay.className = 'barn-full-overlay';
        overlay.innerHTML = `<div class="barn-full-card confirm-card"><p>${message}</p><div class="confirm-actions"><button class="confirm-yes">Yes</button><button class="confirm-no">Cancel</button></div></div>`;
        document.body.appendChild(overlay);
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });
        overlay.querySelector('.confirm-no').addEventListener('click', () => overlay.remove());
        overlay.querySelector('.confirm-yes').addEventListener('click', () => { overlay.remove(); onConfirm(); });
    }

    if (changeBtn) {
        changeBtn.addEventListener('click', () => {
            fetch(`slotstype/slotstype.php?slot=${slotId}&ajax=1`)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    const panel = container.querySelector('#st-slotstype-panel');
                    if (window.initSlotstype && panel) {
                        window.initSlotstype(panel);
                    }
                });
        });
    }

    if (shopBtn) {
        shopBtn.addEventListener('click', () => {
            fetch(`quickshop/quickshop.php?slot=${slotId}&ajax=1`)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    if (!document.getElementById('quickshop-css')) {
                        const link = document.createElement('link');
                        link.id = 'quickshop-css';
                        link.rel = 'stylesheet';
                        link.href = 'quickshop/quickshop.css';
                        document.head.appendChild(link);
                    }
                    const panel = container.querySelector('#quickshop-panel');
                    const init = () => {
                        if (window.initQuickShop && panel) {
                            window.initQuickShop(panel);
                        }
                    };
                    if (!window.initQuickShop) {
                        const script = document.createElement('script');
                        script.src = 'quickshop/quickshop.js';
                        script.onload = init;
                        document.head.appendChild(script);
                    } else {
                        init();
                    }
                });
        });
    }

    if (harvestBtn && slotNum) {
        harvestBtn.addEventListener('click', () => {
            fetch('harvest.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slot: slotNum })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const overlay = container.parentElement;
                        const slotEl = document.getElementById(`slot-${slotNum}`);
                        if (data.xpGain && window.showFloatingText && slotEl) {
                            window.showFloatingText(slotEl, { xp: data.xpGain });
                        }
                        document.dispatchEvent(new CustomEvent('barnAddItem', { detail: data.item }));
                        document.dispatchEvent(new CustomEvent('barnUpdated'));
                        const evt = new CustomEvent('slotUpdated', {
                            detail: { slotId: slotNum, image: data.slotImage, type: 'remove' }
                        });
                        document.dispatchEvent(evt);
                        if (overlay) {
                            overlay.classList.remove('active');
                            overlay.innerHTML = '';
                        }
                        if (content) {
                            content.classList.remove('no-scroll');
                        }
                        if (data.levelUp && window.showLevelUp) {
                            window.showLevelUp(data.newLevel);
                        }
                    } else if (data.error === 'barn_full') {
                        const msgOverlay = document.createElement('div');
                        msgOverlay.className = 'barn-full-overlay';
                        msgOverlay.innerHTML = '<div class="barn-full-card">Barn full</div>';
                        document.body.appendChild(msgOverlay);
                        msgOverlay.addEventListener('click', e => {
                            if (e.target === msgOverlay) msgOverlay.remove();
                        });
                    } else {
                        alert('Harvest failed');
                    }
                })
                .catch(() => alert('Harvest request failed'));
        });
    }

    if (harvestAllBtn && slotNum) {
        harvestAllBtn.addEventListener('click', () => {
            if (!isVip) {
                const msgOverlay = document.createElement('div');
                msgOverlay.className = 'barn-full-overlay';
                msgOverlay.innerHTML = '<div class="barn-full-card">You are not VIP</div>';
                document.body.appendChild(msgOverlay);
                msgOverlay.addEventListener('click', e => {
                    if (e.target === msgOverlay) msgOverlay.remove();
                });
                return;
            }
            fetch('harvest_bulk.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slot: slotNum })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const overlay = container.parentElement;
                        if (Array.isArray(data.slots)) {
                            data.slots.forEach(s => {
                                const slotEl = document.getElementById(`slot-${s.slot}`);
                                if (slotEl && data.xpPerSlot && window.showFloatingText) {
                                    window.showFloatingText(slotEl, { xp: data.xpPerSlot });
                                }
                                const evt = new CustomEvent('slotUpdated', {
                                    detail: { slotId: s.slot, image: s.image, type: 'remove' }
                                });
                                document.dispatchEvent(evt);
                            });
                        }
                        document.dispatchEvent(new CustomEvent('barnAddItem', { detail: data.item }));
                        document.dispatchEvent(new CustomEvent('barnUpdated'));
                        if (overlay) {
                            overlay.classList.remove('active');
                            overlay.innerHTML = '';
                        }
                        if (content) {
                            content.classList.remove('no-scroll');
                        }
                        if (data.levelUp && window.showLevelUp) {
                            window.showLevelUp(data.newLevel);
                        }
                    } else if (data.error === 'barn_full') {
                        const msgOverlay = document.createElement('div');
                        msgOverlay.className = 'barn-full-overlay';
                        msgOverlay.innerHTML = '<div class="barn-full-card">Barn full</div>';
                        document.body.appendChild(msgOverlay);
                        msgOverlay.addEventListener('click', e => {
                            if (e.target === msgOverlay) msgOverlay.remove();
                        });
                    } else {
                        alert('Harvest failed');
                    }
                })
                .catch(() => alert('Harvest request failed'));
        });
    }

    if (removeBtn && slotNum) {
        removeBtn.addEventListener('click', () => {
            showConfirm('Are you sure you want to delete this item?', () => {
                fetch('quickshop/remove_item.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slot: slotNum })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const overlay = container.parentElement;
                            const evt = new CustomEvent('slotUpdated', {
                                detail: { slotId: slotNum, image: data.slotImage, type: 'remove' }
                            });
                            document.dispatchEvent(evt);
                            if (overlay) {
                                fetch(`changeslots/slot-panel.php?slot=${slotNum}&ajax=1`)
                                    .then(res => res.text())
                                    .then(html => {
                                        overlay.innerHTML = html;
                                        const panel = overlay.querySelector('#cs-slot-panel');
                                        if (window.initSlotPanel && panel) {
                                            window.initSlotPanel(panel);
                                        }
                                    });
                            }
                        } else {
                            alert('Remove failed');
                        }
                    })
                    .catch(() => alert('Remove request failed'));
            });
        });
    }

    if (removeAllBtn && slotNum) {
        removeAllBtn.addEventListener('click', () => {
            showConfirm('Are you sure you want to delete these plantings?', () => {
                fetch('quickshop/remove_item_bulk.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slot: slotNum })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const overlay = container.parentElement;
                            if (Array.isArray(data.slots)) {
                                data.slots.forEach(s => {
                                    const evt = new CustomEvent('slotUpdated', {
                                        detail: { slotId: s.slot, image: s.image, type: 'remove' }
                                    });
                                    document.dispatchEvent(evt);
                                });
                            }
                            if (overlay) {
                                fetch(`changeslots/slot-panel.php?slot=${slotNum}&ajax=1`)
                                    .then(res => res.text())
                                    .then(html => {
                                        overlay.innerHTML = html;
                                        const panel = overlay.querySelector('#cs-slot-panel');
                                        if (window.initSlotPanel && panel) {
                                            window.initSlotPanel(panel);
                                        }
                                    });
                            }
                        } else {
                            alert('Remove failed');
                        }
                    })
                    .catch(() => alert('Remove request failed'));
            });
        });
    }

    container.querySelectorAll('.cs-slot-btn').forEach(btn => {
        if (btn !== changeBtn && btn !== shopBtn && btn !== removeBtn && btn !== removeAllBtn && btn !== harvestBtn && btn !== harvestAllBtn) {
            btn.addEventListener('click', () => {
                alert('Functionality coming soon');
            });
        }
    });
}

window.initSlotPanel = initSlotPanel;
