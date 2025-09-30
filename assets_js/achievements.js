document.addEventListener('DOMContentLoaded', () => {
    const achievementsCard = document.getElementById('achievementsCard');
    const isVisitor = window.isVisitor || false;
    const visitId = window.visitId || null;
    const overlay = document.getElementById('achDetailOverlay');
    const overlayImg = document.getElementById('achDetailImage');
    const progressFill = document.getElementById('achProgressFill');
    const progressText = document.getElementById('achProgressText');
    const detailText = document.getElementById('achDetailText');
    const overlayClose = document.getElementById('achDetailClose');

    if (overlayClose && overlay) {
        overlayClose.addEventListener('click', () => {
            overlay.style.display = 'none';
        });
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.style.display = 'none';
            }
        });
    }

    const updateMiniCard = (img) => {
        if (!achievementsCard) return;
        achievementsCard.innerHTML = '';
        const imageEl = document.createElement('img');
        imageEl.src = img || 'img/achievements/default.png';
        imageEl.alt = 'Achievement';
        imageEl.style.width = '100%';
        imageEl.style.height = '100%';
        imageEl.style.objectFit = 'cover';
        achievementsCard.appendChild(imageEl);
    };

    const updateButtons = (selectedId) => {
        document.querySelectorAll('.ach-item').forEach(item => {
            const applyBtn = item.querySelector('.ach-apply-btn');
            const removeBtn = item.querySelector('.ach-remove-btn');
            if (item.dataset.id === selectedId) {
                if (applyBtn) applyBtn.style.display = 'none';
                if (removeBtn) removeBtn.style.display = 'block';
            } else {
                if (applyBtn) applyBtn.style.display = 'block';
                if (removeBtn) removeBtn.style.display = 'none';
            }
        });
    };

    if (!isVisitor) {
        document.querySelectorAll('.ach-apply-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.ach-item');
                if (!item) return;
                const id = item.dataset.id;
                fetch('apply_achievement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(id),
                    credentials: 'same-origin'
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        updateButtons(id);
                        updateMiniCard(data.image);
                    }
                });
            });
        });

        document.querySelectorAll('.ach-remove-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const item = btn.closest('.ach-item');
                if (!item) return;
                const id = item.dataset.id;
                fetch('remove_achievement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + encodeURIComponent(id),
                    credentials: 'same-origin'
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        updateButtons(null);
                        updateMiniCard(data.image);
                    }
                });
            });
        });
    }

    document.querySelectorAll('.ach-item[data-id]').forEach(item => {
        item.addEventListener('click', (e) => {
            if (e.target.closest('.ach-btn')) return; // ignore button clicks
            const id = item.dataset.id;
            if (!id || !overlay) return;
            const url = 'achievement_details.php?id=' + encodeURIComponent(id) +
                (isVisitor && visitId ? '&user=' + encodeURIComponent(visitId) : '');
            fetch(url, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;
                    overlayImg.src = data.image || '';
                    progressFill.style.width = (data.progress || 0) + '%';
                    progressText.textContent = Math.round(data.progress || 0) + '%';
                    detailText.textContent = data.detail || '';
                    overlay.style.display = 'flex';
                });
        });
    });
});