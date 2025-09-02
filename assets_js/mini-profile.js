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

    const achievementsCard = document.getElementById('achievementsCard');
    if (achievementsCard) {
        achievementsCard.addEventListener('click', () => {
            const achUrl = (window.baseUrl || '') +
                (window.isVisitor && window.visitId
                    ? ('view_achievements.php?id=' + window.visitId)
                    : 'achievements.php');
            window.location.href = achUrl;
        });
    }

    let slideTimeout;
    let snapTimeout;
    let animationFrame;
    const startSlider = () => {
        if (!helpersCard) return;
        clearTimeout(slideTimeout);
        clearTimeout(snapTimeout);
        cancelAnimationFrame(animationFrame);
        helpersCard.scrollLeft = 0;

        const items = helpersCard.querySelectorAll('.helper-item');
        if (!items.length) return;

        const gap = parseInt(getComputedStyle(helpersCard).gap) || 0;
        const itemWidth = items[0].offsetWidth + gap;
        let index = 0;
        const pause = 4000;
        const resumeDelay = 4000;
        const duration = 1000;
        let isDragging = false;
        let startX = 0;
        let scrollStart = 0;

        const animateScroll = (from, to, time, callback) => {
            const start = performance.now();
            const frame = (now) => {
                const progress = Math.min((now - start) / time, 1);
                helpersCard.scrollLeft = from + (to - from) * progress;
                if (progress < 1) {
                    animationFrame = requestAnimationFrame(frame);
                } else if (callback) {
                    callback();
                }
            };
            animationFrame = requestAnimationFrame(frame);
        };

        const snap = () => {
            index = Math.max(0, Math.min(items.length - 1, Math.round(helpersCard.scrollLeft / itemWidth)));
            animateScroll(helpersCard.scrollLeft, index * itemWidth, duration, () => {
                slideTimeout = setTimeout(schedule, pause);
            });
        };

        const onDragStart = (clientX) => {
            isDragging = true;
            startX = clientX;
            scrollStart = helpersCard.scrollLeft;
            clearTimeout(slideTimeout);
            clearTimeout(snapTimeout);
            cancelAnimationFrame(animationFrame);
        };

        const onDragMove = (clientX) => {
            if (!isDragging) return;
            helpersCard.scrollLeft = scrollStart - (clientX - startX);
        };

        const onDragEnd = () => {
            if (!isDragging) return;
            isDragging = false;
            clearTimeout(snapTimeout);
            snapTimeout = setTimeout(snap, resumeDelay);
        };

        helpersCard.addEventListener('mousedown', (e) => onDragStart(e.pageX));
        helpersCard.addEventListener('touchstart', (e) => onDragStart(e.touches[0].pageX));
        helpersCard.addEventListener('mousemove', (e) => onDragMove(e.pageX));
        helpersCard.addEventListener('touchmove', (e) => onDragMove(e.touches[0].pageX));
        ['mouseleave', 'mouseup'].forEach(ev => helpersCard.addEventListener(ev, onDragEnd));
        ['touchend', 'touchcancel'].forEach(ev => helpersCard.addEventListener(ev, onDragEnd));

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
