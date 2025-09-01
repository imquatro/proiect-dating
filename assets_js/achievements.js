document.addEventListener('DOMContentLoaded', () => {
    const achievementsCard = document.getElementById('achievementsCard');
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
});