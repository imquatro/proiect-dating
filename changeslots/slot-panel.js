function initSlotPanel(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    const changeBtn = container.querySelector('#cs-slot-change');
    const shopBtn = container.querySelector('#cs-slot-shop');
    const removeBtn = container.querySelector('#cs-slot-remove');
    const harvestBtn = container.querySelector('#cs-slot-harvest');
    const slotImage = container.querySelector('#cs-slot-image');
    const slotId = slotImage ? slotImage.alt.replace(/\D/g, '') : '';
    const slotNum = parseInt(slotId, 10);

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
                    } else {
                        alert('Harvest failed');
                    }
                })
                .catch(() => alert('Harvest request failed'));
        });
    }

    if (removeBtn && slotNum) {
        removeBtn.addEventListener('click', () => {
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
    }

    container.querySelectorAll('.cs-slot-btn').forEach(btn => {
        if (btn !== changeBtn && btn !== shopBtn && btn !== removeBtn && btn !== harvestBtn) {
            btn.addEventListener('click', () => {
                alert('Functionality coming soon');
            });
        }
    });
}

window.initSlotPanel = initSlotPanel;