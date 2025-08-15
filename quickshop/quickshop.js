function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
      const slotId = container.dataset.slotId;
    const overlay = document.getElementById('slot-panel-overlay');
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

    const helper = container.querySelector('.qs-helper');
    if (helper) {
        let timer;
        const start = () => {
            timer = setTimeout(() => {
                const uid = helper.dataset.userId;
                if (uid) {
                    window.location.href = `vizitfarm/vizitfarm.php?id=${uid}`;
                }
            }, 1000);
        };
        const cancel = () => clearTimeout(timer);
        helper.addEventListener('mousedown', start);
        helper.addEventListener('touchstart', start);
        ['mouseup', 'mouseleave', 'mouseout', 'touchend', 'touchcancel'].forEach(ev => {
            helper.addEventListener(ev, cancel);
        });
    }

    function plantItem(itemId, price, itemElem) {
        const water = itemElem.dataset.water;
        const feed = itemElem.dataset.feed;
        const waterTimes = itemElem.dataset.waterTimes;
        const feedTimes = itemElem.dataset.feedTimes;

        fetch('quickshop/plant_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot: slotId, item: itemId, price: price })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const evt = new CustomEvent('slotUpdated', {
                        detail: {
                            slotId: slotId,
                            image: data.image,
                            waterInterval: water,
                            feedInterval: feed,
                            waterTimes: waterTimes,
                            feedTimes: feedTimes
                        }
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
                    console.error('Plant failed');
                }
            });
    }

    container.querySelectorAll('.quickshop-item').forEach(item => {
        const id = item.dataset.itemId;
        const price = item.dataset.price;
        item.addEventListener('click', () => {
            plantItem(id, price, item);
        });
        const buyBtn = item.querySelector('.qs-buy');
        if (buyBtn) {
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                plantItem(id, price, item);
            });
        }
    });
}

window.initQuickShop = initQuickShop;