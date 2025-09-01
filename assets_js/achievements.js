document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.apply-ach-btn').forEach(btn => {
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
                    document.querySelectorAll('.apply-ach-btn').forEach(b => { b.disabled = false; b.textContent = 'Apply'; });
                    btn.disabled = true;
                    btn.textContent = 'Selected';
                }
            });
        });
    });
});