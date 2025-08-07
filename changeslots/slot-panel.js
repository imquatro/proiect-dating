function initSlotPanel(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    container.querySelectorAll('.cs-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            alert('Functionality coming soon');
        });
    });
}

window.initSlotPanel = initSlotPanel;