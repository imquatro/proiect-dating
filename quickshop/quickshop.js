function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
    const slotId = container.dataset.slotId;
    const overlay = document.getElementById('slot-panel-overlay');

    function plantItem(itemId) {
        fetch('quickshop/plant_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot: slotId, item: itemId })
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
        item.addEventListener('click', () => {
            plantItem(id);
        });
        const buyBtn = item.querySelector('.qs-buy');
        if (buyBtn) {
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                plantItem(id);
            });
        }
    });
}

window.initQuickShop = initQuickShop;