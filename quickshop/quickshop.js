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
        const buyBtn = item.querySelector('.qs-buy');
        if(buyBtn){
            buyBtn.addEventListener('click', e => {
                e.stopPropagation();
                const id = item.dataset.itemId;
                console.log('Buy/Use item', id);
            });
        }
    });
}

window.initQuickShop = initQuickShop;