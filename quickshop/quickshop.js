function initQuickShop(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }
}

window.initQuickShop = initQuickShop;