document.addEventListener('DOMContentLoaded', () => {
    const achievementsCard = document.getElementById('achievementsCard');
    const overlay = document.getElementById('achDetailOverlay');
    const overlayImg = document.getElementById('achDetailImage');
        achievementsCard.innerHTML = '';
        const imageEl = document.createElement('img');
        imageEl.src = img || 'img/achievements/default.png';
        imageEl.alt = 'Achievement';
        imageEl.style.width = '100%';
        imageEl.style.height = '100%';
        imageEl.style.objectFit = 'cover';
        achievementsCard.appendChild(imageEl);
    };
            if (overlay) overlay.style.display = 'none';
        });
    }
    const updateMiniCard = (img) => {
        if (!achievementsCard) return;
        achievementsCard.innerHTML = '';
        const imageEl = document.createElement('img');
        imageEl.src = img || 'img/achievements/default.png';
        imageEl.alt = 'Achievement';
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

    document.querySelectorAll('.ach-apply-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.ach-item');
            if (!item) return;
            const id = item.dataset.id;
            fetch('apply_achievement.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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

    // Clicking any achievement to show details
    document.querySelectorAll('.ach-item[data-id]').forEach(item => {
        item.addEventListener('click', (e) => {
            if (e.target.closest('.ach-btn')) return; // ignore button clicks
            const id = item.dataset.id;
            if (!id || !overlay) return;
            fetch('achievement_details.php?id=' + encodeURIComponent(id), { credentials: 'same-origin' })
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