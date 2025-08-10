function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
    container.querySelectorAll('.quickshop-item').forEach(item => {
        item.addEventListener('click', () => {
            const id = item.dataset.itemId;
            console.log('Selected item', id);
        });
    });
}

window.initQuickShop = initQuickShop;