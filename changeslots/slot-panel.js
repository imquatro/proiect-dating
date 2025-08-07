function initSlotPanel(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    const changeBtn = container.querySelector('#cs-slot-change');
    const slotImage = container.querySelector('#cs-slot-image');
    const slotId = slotImage ? slotImage.alt.replace(/\D/g, '') : '';

    if (changeBtn) {
        changeBtn.addEventListener('click', () => {
            fetch(`slotstype/slotstype.php?slot=${slotId}&ajax=1`)
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    if (window.initSlotstype) {
                        window.initSlotstype(container);
                    }
                });
        });
    }

    container.querySelectorAll('.cs-slot-btn').forEach(btn => {
        if (btn !== changeBtn) {
            btn.addEventListener('click', () => {
                alert('Functionality coming soon');
            });
        }
    });
}

window.initSlotPanel = initSlotPanel;