document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('helpersList');
    const settings = document.getElementById('helperSettings');
    if (!list || !settings) return;

    const applyBtn = document.createElement('button');
    applyBtn.id = 'applyHelperBtn';
    applyBtn.textContent = 'Apply';
    applyBtn.style.display = 'none';
    settings.appendChild(applyBtn);

    let selectedId = null;

    applyBtn.addEventListener('click', () => {
        if (!selectedId) return;
        fetch('apply_helper.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'helper_id=' + encodeURIComponent(selectedId)
        })
            .then(r => r.json())
            .then(resp => {
                if (resp.success) {
                    list.querySelectorAll('.helper-card').forEach(c => c.classList.remove('selected'));
                    const sel = list.querySelector(`.helper-card[data-id="${selectedId}"]`);
                    if (sel) sel.classList.add('selected');
                }
            });
    });

    fetch('helpers_list.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            data.helpers.forEach(h => {
                const card = document.createElement('div');
                card.className = 'helper-card';
                card.dataset.id = h.id;
                card.innerHTML = `<img src="${h.image}" alt="${h.name}"><span>${h.name}</span>`;
                card.addEventListener('click', () => {
                    list.querySelectorAll('.helper-card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                    selectedId = h.id;
                    applyBtn.style.display = 'block';
                });
                list.appendChild(card);
            });
        })
        .catch(() => {});
});