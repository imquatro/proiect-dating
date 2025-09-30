document.addEventListener('DOMContentLoaded', () => {
    const avatar = document.querySelector('.mini-profile-avatar');
    if (avatar) {
        avatar.addEventListener('click', () => {
            console.log('Mini profile avatar clicked');
        });
    }

    const helpersCard = document.getElementById('helpersCard');
    const helpersUrl = (window.baseUrl || '') + 'recent_helpers.php';

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
            })
            .catch(() => {
                helpersCard.innerHTML = '';
            });
    };

    if (helpersCard) {
        loadHelpers();
        setInterval(loadHelpers, 5000);

        let isDown = false;
        let startX = 0;
        let scrollLeft = 0;

        helpersCard.addEventListener('pointerdown', (e) => {
            isDown = true;
            startX = e.clientX;
            scrollLeft = helpersCard.scrollLeft;
            helpersCard.style.cursor = 'grabbing';
            helpersCard.setPointerCapture(e.pointerId);
        });

        helpersCard.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            helpersCard.scrollLeft = scrollLeft - dx;
            e.preventDefault();
        });

        const endDrag = () => {
            isDown = false;
            helpersCard.style.cursor = 'grab';
        };

        helpersCard.addEventListener('pointerup', endDrag);
        helpersCard.addEventListener('pointerleave', endDrag);
        helpersCard.addEventListener('pointercancel', endDrag);
    }
});