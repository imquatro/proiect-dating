document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const miniProfile = document.querySelector('.mini-profile');
    const topNav = document.querySelector('.top-bar');
    const bottomNav = document.querySelector('.bottom-nav');
    const content = document.querySelector('.content');
    const divider = document.querySelector('.farm-divider');
    const farmSlots = document.querySelector('.farm-slots');
    const visitId = farmSlots ? farmSlots.dataset.user : null;

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

    function refreshSlots() {
        if (!visitId) return;
        fetch(`vizitfarm/slots_state.php?id=${visitId}`)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.slots) return;
                data.slots.forEach(slot => {
                    const slotEl = document.getElementById(`slot-${slot.id}`);
                    if (!slotEl) return;
                    const img = slotEl.querySelector('img');
                    if (img && img.getAttribute('src') !== slot.image) {
                        img.setAttribute('src', slot.image);
                    }
                    if (slot.unlocked) {
                        slotEl.classList.remove('locked');
                        const overlay = slotEl.querySelector('.slot-overlay');
                        if (overlay) overlay.remove();
                    } else {
                        slotEl.classList.add('locked');
                        let overlay = slotEl.querySelector('.slot-overlay');
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'slot-overlay';
                            slotEl.appendChild(overlay);
                        }
                        if (slot.premium) {
                            overlay.innerHTML = '<img src="img/gold.png" alt="Gold">';
                        } else {
                            overlay.textContent = `Level ${slot.required}`;
                        }
                    }
                });
            })
            .catch(err => console.error(err));
    }

    refreshSlots();
    setInterval(refreshSlots, 3000);
});