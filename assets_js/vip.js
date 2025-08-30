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
    panel.querySelectorAll('.vip-sub-tabs').forEach(sub => {
        initTabs(sub.parentElement, '.sub-tab-btn', '.subtab-content', 'data-subtab');
    });

    const grid = panel.querySelector('.vip-frame-grid');
    if (grid) {
        grid.addEventListener('click', e => {
            const btn = e.target.closest('.apply-frame-btn');
            if (!btn) return;
            const frame = btn.dataset.frame;
            fetch('apply_frame.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'frame=',
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
            })
            .catch(err => console.error(err));
        });
    }

    const removeCardBtn = panel.querySelector('#removeCardBtn');
    if (removeCardBtn) {
        removeCardBtn.addEventListener('click', () => {
            fetch('apply_card.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'card=',
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
            })
            .catch(err => console.error(err));
        });
    }
});
