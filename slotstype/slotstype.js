function initSlotstype(container) {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    const slotId = container.dataset.slot;
    const overlay = container.closest('#slot-panel-overlay');

    container.querySelectorAll('.st-slotstype-item').forEach(item => {
        const applyBtn = item.querySelector('.st-slot-apply');

        item.addEventListener('click', () => {
            container.querySelectorAll('.st-slotstype-item').forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
        });

        if (applyBtn) {
            applyBtn.addEventListener('click', e => {
                e.stopPropagation();
                const type = item.dataset.type;
                fetch(`slotstype/${type}/${type}.php?slot=${slotId}&apply=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const slotImg = document.querySelector(`#slot-${slotId} img`);
                            if (slotImg && data.image) {
                                slotImg.src = data.image;
                            }
                            document.dispatchEvent(new CustomEvent('slotUpdated', {
                                detail: { slotId, type, image: data.image }
                            }));
                            if (overlay) {
                                overlay.classList.remove('active');
                                overlay.innerHTML = '';
                            }
                            if (content) {
                                content.classList.remove('no-scroll');
                            }
                        } else if (data && data.error) {
                            alert(data.error);
                        }
                    })
                    .catch(err => console.error(err));
            });
        }
    });
}


window.initSlotstype = initSlotstype;