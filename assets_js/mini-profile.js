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

    let slideTimeout;
    const startSlider = () => {
        if (!helpersCard) return;
        clearTimeout(slideTimeout);
        helpersCard.scrollLeft = 0;

        const items = helpersCard.querySelectorAll('.helper-item');
        if (!items.length) return;

        const itemWidth = helpersCard.clientWidth;
        let index = 0;
        const pause = 4000;
        const duration = 1000;

        const animateScroll = (from, to, time, callback) => {
            const start = performance.now();
            const frame = (now) => {
                const progress = Math.min((now - start) / time, 1);
                helpersCard.scrollLeft = from + (to - from) * progress;
                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else if (callback) {
                    callback();
                }
            };
            requestAnimationFrame(frame);
        };

        const schedule = () => {
            const nextIndex = (index + 1) % items.length;
            animateScroll(index * itemWidth, nextIndex * itemWidth, duration, () => {
                index = nextIndex;
                slideTimeout = setTimeout(schedule, pause);
            });
        };

        slideTimeout = setTimeout(schedule, pause);
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
