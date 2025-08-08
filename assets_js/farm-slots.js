document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const miniProfile = document.querySelector('.mini-profile');
    const topNav = document.querySelector('.top-bar');
    const bottomNav = document.querySelector('.bottom-nav');
    const content = document.querySelector('.content');
    const divider = document.querySelector('.farm-divider');
    const farmSlots = document.querySelector('.farm-slots');
    const overlay = document.createElement('div');
    overlay.id = 'slot-panel-overlay';
    document.body.appendChild(overlay);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            overlay.innerHTML = '';
            if (content) {
                content.classList.remove('no-scroll');
            }
        }
    });

    function updateSlotSize(iter = 0) {
        if (!farmSlots) return;

        const styles = getComputedStyle(root);
        const gap = parseFloat(styles.getPropertyValue('--slot-gap')) || 0;

        const topNavHeight = topNav ? topNav.offsetHeight : 0;
        const bottomNavHeight = bottomNav ? bottomNav.offsetHeight : 0;
        const miniProfileHeight = miniProfile ? miniProfile.offsetHeight : 0;
        const miniProfileMargin = miniProfile ? parseFloat(getComputedStyle(miniProfile).marginBottom) : 0;

        const dividerHeight = divider ? divider.offsetHeight : 0;
        const dividerMargin = divider ? parseFloat(getComputedStyle(divider).marginTop) + parseFloat(getComputedStyle(divider).marginBottom) : 0;

        const farmStyles = getComputedStyle(farmSlots);
        const farmPaddingV = parseFloat(farmStyles.paddingTop) + parseFloat(farmStyles.paddingBottom);
        const farmPaddingH = parseFloat(farmStyles.paddingLeft) + parseFloat(farmStyles.paddingRight);

        const rows = farmSlots.querySelectorAll('.farm-row').length || 0;
        const firstRow = farmSlots.querySelector('.farm-row');
        const columns = firstRow ? firstRow.children.length : 0;

        if (!rows || !columns) return;

        const verticalGaps = gap * Math.max(rows - 1, 0);
        const availableHeight = window.innerHeight - topNavHeight - bottomNavHeight - miniProfileHeight - miniProfileMargin - dividerHeight - dividerMargin - farmPaddingV - verticalGaps;
        const slotSizeByHeight = availableHeight / rows;

        const containerWidth = content ? content.clientWidth : window.innerWidth;
        const horizontalGaps = gap * Math.max(columns - 1, 0);
        const slotSizeByWidth = (containerWidth - farmPaddingH - horizontalGaps) / columns;

        const slotSize = Math.min(slotSizeByHeight, slotSizeByWidth);
        root.style.setProperty('--slot-size', `${slotSize}px`);

        if (iter < 2) {
            requestAnimationFrame(() => updateSlotSize(iter + 1));
        }
    }

    updateSlotSize();
    window.addEventListener('resize', () => updateSlotSize());

    document.addEventListener('slotUpdated', e => {
        const { slotId, image } = e.detail || {};
        if (!slotId || !image) return;
        const slotImg = document.querySelector(`#slot-${slotId} img`);
        if (slotImg) {
            slotImg.src = image;
        }
    });

    document.querySelectorAll('.farm-slot:not(.locked)').forEach(slot => {
        slot.addEventListener('click', () => {
            const slotId = slot.id.replace('slot-', '');
            fetch(`changeslots/slot-panel.php?slot=${slotId}&ajax=1`)
                .then(res => res.text())
                .then(html => {
                    overlay.innerHTML = html;
                    overlay.classList.add('active');
                    if (window.initSlotPanel) {
                        window.initSlotPanel(overlay);
                    }
                });
        });
    });
});
