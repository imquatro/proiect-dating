document.addEventListener('DOMContentLoaded', () => {
    const avatar = document.querySelector('.mini-profile-avatar');
    if (avatar) {
        avatar.addEventListener('click', () => {
            console.log('Mini profile avatar clicked');
        });
    }

    const helpersCard = document.getElementById('helpersCard');
    const helpersUrl = (window.baseUrl || '') + 'recent_helpers.php' +
        (window.isVisitor && window.visitId ? ('?user_id=' + window.visitId) : '');

    let sliderInterval;
    const startSlider = () => {
        if (!helpersCard) return;
        clearInterval(sliderInterval);
        helpersCard.scrollLeft = 0;
        const step = () => {
            const maxScroll = helpersCard.scrollWidth - helpersCard.clientWidth;
            if (helpersCard.scrollLeft >= maxScroll) {
                clearInterval(sliderInterval);
                setTimeout(() => {
                    helpersCard.scrollLeft = 0;
                    startSlider();
                }, 1000);
            } else {
                helpersCard.scrollLeft += 0.3;
            }
        };
        sliderInterval = setInterval(step, 40);
    };

    const loadHelpers = () => {
        if (!helpersCard) return;
        fetch(helpersUrl, { credentials: 'same-origin' })
            .then(response => response.json())
            .then(data => {
                helpersCard.innerHTML = '';
                if (!Array.isArray(data)) return;
                data.sort((a, b) => new Date(b.last) - new Date(a.last));
                data.forEach(helper => {
                    const item = document.createElement('div');
                    item.className = 'helper-item';
                    const total = (helper.feed || 0) + (helper.water || 0);
                    item.innerHTML = `
                        <img src="${helper.photo}" alt="${helper.username}">
                        <div class="helper-total">${total}</div>
                        <div class="helper-counts">🍖 ${helper.feed} | 💧 ${helper.water}</div>`;
                    helpersCard.appendChild(item);
                });
                startSlider();
            })
            .catch(() => {
                helpersCard.innerHTML = '';
            });
    };

    if (helpersCard) {
        loadHelpers();
    }
});
