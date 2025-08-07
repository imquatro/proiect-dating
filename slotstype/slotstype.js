function initSlotstype(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
    container.querySelectorAll('.st-slotstype-item').forEach(item => {
        item.addEventListener('click', () => {
            alert('Slot type selection coming soon');
        });
    });
}

window.initSlotstype = initSlotstype;