function initSlotPanel(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    const changeBtn = container.querySelector('#cs-slot-change');
    const shopBtn = container.querySelector('#cs-slot-shop');
    const slotImage = container.querySelector('#cs-slot-image');
    const slotId = slotImage ? slotImage.alt.replace(/\D/g, '') : '';

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
                    const panel = container.querySelector('#quickshop-panel');
                    if (window.initQuickShop && panel) {
                        window.initQuickShop(panel);
                    }
                });
        });
    }

    container.querySelectorAll('.cs-slot-btn').forEach(btn => {
        if (btn !== changeBtn && btn !== shopBtn) {
            btn.addEventListener('click', () => {
                alert('Functionality coming soon');
            });
        }
    });
}

window.initSlotPanel = initSlotPanel;