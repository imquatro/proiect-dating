function updateSlotSize() {
    const root = document.documentElement;
    const miniProfile = document.querySelector('.mini-profile');
    const topNav = document.querySelector('.top-bar');
    const bottomNav = document.querySelector('.bottom-nav');
    const content = document.querySelector('.content');
    const divider = document.querySelector('.farm-divider');
    const farmSlots = document.querySelector('.farm-slots');

    const styles = getComputedStyle(root);
    const gap = parseFloat(styles.getPropertyValue('--slot-gap')) || 0;

    const topNavHeight = topNav ? topNav.offsetHeight : 0;
    const bottomNavHeight = bottomNav ? bottomNav.offsetHeight : 0;
    const miniProfileHeight = miniProfile ? miniProfile.offsetHeight : 0;
    const miniProfileMargin = miniProfile ? parseFloat(getComputedStyle(miniProfile).marginBottom) : 0;
    const dividerMargin = divider ? parseFloat(getComputedStyle(divider).marginTop) + parseFloat(getComputedStyle(divider).marginBottom) : 0;
    const farmPadding = farmSlots ? parseFloat(getComputedStyle(farmSlots).paddingTop) + parseFloat(getComputedStyle(farmSlots).paddingBottom) : 0;

    const verticalGaps = gap * 6; // gaps between 7 rows
    const availableHeight = window.innerHeight - topNavHeight - bottomNavHeight - miniProfileHeight - miniProfileMargin - dividerMargin - farmPadding - verticalGaps;
    const slotSizeByHeight = availableHeight / 7;

    const containerWidth = content ? content.clientWidth : window.innerWidth;
    const slotSizeByWidth = (containerWidth - gap * 2) / 3;

    const slotSize = Math.min(slotSizeByHeight, slotSizeByWidth);
    root.style.setProperty('--slot-size', `${slotSize}px`);
}

    updateFarmSlotSize();
    window.requestAnimationFrame(updateFarmSlotSize);
    document.querySelectorAll('.farm-slot').forEach((slot) => {
        slot.addEventListener('click', () => {
            window.location.href = 'changeslots/slot-panel.php';
        });
    });
});


window.addEventListener('resize', updateSlotSize);