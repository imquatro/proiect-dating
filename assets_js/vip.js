document.addEventListener('DOMContentLoaded', function () {
    function initTabs(container, btnSelector, contentSelector, attr) {
        const btns = container.querySelectorAll(btnSelector);
        const contents = container.querySelectorAll(contentSelector);
        btns.forEach(btn => {
            btn.addEventListener('click', () => {
                btns.forEach(b => b.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                const target = btn.getAttribute(attr);
                const el = container.querySelector('#' + target);
                if (el) el.classList.add('active');
            });
        });
    }

    const panel = document.getElementById('vipPanel');
    if (!panel) return;

    initTabs(panel, '.tab-btn', '.tab-content', 'data-tab');
    initTabs(panel, '.sub-tab-btn', '.subtab-content', 'data-subtab');

    const preview = document.getElementById('vipPreview');
    const previewProfile = preview ? preview.querySelector('#miniProfile') : null;
    const previewFrame = previewProfile ? previewProfile.querySelector('.mini-profile-frame') : null;
    const previewCard = previewProfile ? previewProfile.querySelector('.mini-profile-card') : null;
    const originalFrameSrc = previewFrame ? previewFrame.src : '';
    const originalCardBg = previewCard ? previewCard.style.backgroundImage : '';

    let selectedFrame = null;
    let selectedCard = null;

    const frameItems = panel.querySelectorAll('.vip-frame-item');
    const initialFrame = panel.querySelector('.vip-frame-item.selected');
    if (initialFrame) {
        selectedFrame = initialFrame.dataset.frame;
    }
    frameItems.forEach(item => {
        item.addEventListener('click', e => {
            if (e.target.closest('button')) return;
            const frame = item.dataset.frame;
            if (!frame || !previewFrame) return;
            if (selectedFrame === frame) {
                selectedFrame = null;
                item.classList.remove('selected');
                previewFrame.src = originalFrameSrc;
            } else {
                selectedFrame = frame;
                frameItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                previewFrame.src = frame;
            }
        });
    });

    const cardItems = panel.querySelectorAll('.vip-card-item');
    const initialCard = panel.querySelector('.vip-card-item.selected');
    if (initialCard) {
        selectedCard = initialCard.dataset.card;
    }
    cardItems.forEach(item => {
        item.addEventListener('click', e => {
            if (e.target.closest('button')) return;
            const card = item.dataset.card;
            if (!card || !previewCard) return;
            if (selectedCard === card) {
                selectedCard = null;
                item.classList.remove('selected');
                previewCard.style.backgroundImage = originalCardBg;
            } else {
                selectedCard = card;
                cardItems.forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');
                previewCard.style.backgroundImage = `url('${card}')`;
            }
        });
    });

    function updatePreviewVisibility(tab) {
        if (!preview) return;
        if (tab === 'frames' || tab === 'cards') {
            preview.style.display = 'flex';
        } else {
            preview.style.display = 'none';
        }
    }

    const subTabBtns = panel.querySelectorAll('.sub-tab-btn');
    subTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            updatePreviewVisibility(btn.dataset.subtab);
            panel.classList.toggle('benefits-active', btn.dataset.subtab === 'benefits');
        });
    });
    const activeBtn = panel.querySelector('.sub-tab-btn.active');
    if (activeBtn) {
        updatePreviewVisibility(activeBtn.dataset.subtab);
        panel.classList.toggle('benefits-active', activeBtn.dataset.subtab === 'benefits');
    }

    const frameGrid = panel.querySelector('.vip-frame-grid');
    if (frameGrid) {
        frameGrid.addEventListener('click', e => {
            const btn = e.target.closest('.apply-frame-btn');
            if (!btn) return;
            const frame = btn.dataset.frame;
            fetch('apply_frame.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'frame=' + encodeURIComponent(frame),
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.error || 'Error');
            })
            .catch(err => console.error(err));
        });
    }

    const cardGrid = panel.querySelector('.vip-card-grid');
    if (cardGrid) {
        cardGrid.addEventListener('click', e => {
            const btn = e.target.closest('.apply-card-btn');
            if (!btn) return;
            const card = btn.dataset.card;
            fetch('apply_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'card=' + encodeURIComponent(card),
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.error || 'Error');
            })
            .catch(err => console.error(err));
        });
    }

    const removeBtn = panel.querySelector('#removeFrameBtn');
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            fetch('apply_frame.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'frame=',
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => { if (data.success) location.reload(); })
            .catch(err => console.error(err));
        });
    }

    const removeCardBtn = panel.querySelector('#removeCardBtn');
    if (removeCardBtn) {
        removeCardBtn.addEventListener('click', () => {
            fetch('apply_card.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'card=',
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => { if (data.success) location.reload(); })
            .catch(err => console.error(err));
        });
    }
});