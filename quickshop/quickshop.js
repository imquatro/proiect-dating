function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
    const overlay = document.getElementById('slot-panel-overlay');
    const isVip = parseInt(container.dataset.vip, 10) > 0;

    if (container.dataset.planted === '1') {
        const grid = container.querySelector('.quickshop-grid');
        if (grid) {
            grid.style.display = 'none';
        }
        container.querySelectorAll('.cs-slot-btn').forEach(btn => {
            btn.style.display = 'none';
        });
        return;
    }

    function plantItem(itemId, itemElem) {
        const water = itemElem.dataset.water;
        const feed = itemElem.dataset.feed;
        const waterTimes = itemElem.dataset.waterTimes;
        const feedTimes = itemElem.dataset.feedTimes;
        const slotsAttr = itemElem.dataset.slots || '';
        const allSlots = slotsAttr ? slotsAttr.split(',').map(s => parseInt(s)) : [];
        let count = 1;
        const select = itemElem.querySelector('.qs-count');
        if (select) {
            const match = select.value.match(/\d+/);
            count = match ? parseInt(match[0], 10) : 1;
        }
        if (count > 1 && !isVip) {
            alert('You are not VIP');
            return;
        }
        const slots = allSlots.slice(0, count);

        fetch('quickshop/plant_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slots: slots, item: itemId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    slots.forEach(slot => {
                        const evt = new CustomEvent('slotUpdated', {
                            detail: {
                                slotId: slot,
                                image: data.image,
                                waterInterval: water,
                                feedInterval: feed,
                                waterTimes: waterTimes,
                                feedTimes: feedTimes
                            }
                        });
                        document.dispatchEvent(evt);
                    });
                    if (overlay) {
                        overlay.classList.remove('active');
                        overlay.innerHTML = '';
                    }
                    if (content) {
                        content.classList.remove('no-scroll');
                    }
                } else if (data.error) {
                    alert(data.error);
                } else {
                    console.error('Plant failed');
                }
            });
    }

    container.querySelectorAll('.quickshop-item').forEach(item => {
        const id = item.dataset.itemId;
        const buyBtn = item.querySelector('.qs-buy');
        if (buyBtn) {
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                plantItem(id, item);
            });
        }
    });
}

window.initQuickShop = initQuickShop;
