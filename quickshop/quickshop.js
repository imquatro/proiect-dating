function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
    const slotId = container.dataset.slotId;
    const overlay = document.getElementById('slot-panel-overlay');

    function plantItem(itemId, price) {
        fetch('quickshop/plant_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot: slotId, item: itemId, price: price })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const evt = new CustomEvent('slotUpdated', {
                        detail: { slotId: slotId, image: data.image }
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
            plantItem(id, price);
        });
        const buyBtn = item.querySelector('.qs-buy');
        if (buyBtn) {
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                plantItem(id, price);
            });
        }
    });
}

window.initQuickShop = initQuickShop;