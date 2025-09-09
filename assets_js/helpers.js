document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('helpersList');
    const settings = document.getElementById('helperSettings');
    if (!list || !settings) return;

    function refreshInfo() {
        fetch('helper_info.php', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(info => {
                if (info.helper) {
                    settings.innerHTML = `
                        <div class="applied-helper-card">
                            <img src="${info.helper.image}" alt="${info.helper.name}">
                            <div>
                                <p>Water: ${info.waterUsed}/${info.waterLimit}</p>
                                <p>Feed: ${info.feedUsed}/${info.feedLimit}</p>
                                <p>Harvest: ${info.harvestUsed}/${info.harvestLimit}</p>
                            </div>
                        </div>`;
                } else {
                    settings.innerHTML = '';
                }
            })
            .catch(() => { settings.innerHTML = ''; });
    }

    refreshInfo();

    fetch('helpers_list.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            data.helpers.forEach(h => {
                const card = document.createElement('div');
                card.className = 'helper-card';
                card.dataset.id = h.id;
                card.innerHTML = `
                    <img src="${h.image}" alt="${h.name}">
                    <div class="helper-info">
                        <span class="helper-name">${h.name}</span>
                        <div class="helper-stats">
                            <span>Water: ${h.waters}</span>
                            <span>Feed: ${h.feeds}</span>
                            <span>Harvest: ${h.harvests}</span>
                        </div>
                        <button class="apply-helper-btn">Apply</button>
                    </div>
                `;

                const applyBtn = card.querySelector('.apply-helper-btn');
                applyBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    fetch('apply_helper.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'helper_id=' + encodeURIComponent(h.id)
                    })
                        .then(r => r.json())
                        .then(resp => {
                            if (resp.success) {
                                list.querySelectorAll('.helper-card').forEach(c => c.classList.remove('selected'));
                                card.classList.add('selected');

                                const overlay = document.createElement('div');
                                overlay.className = 'helper-overlay';
                                overlay.innerHTML = `
                                    <div class="applied-helper-card">
                                        <img src="${h.image}" alt="${h.name}">
                                        <div><strong>${h.name}</strong><br>This helper is now applied to your farm.</div>
                                    </div>`;
                                document.body.appendChild(overlay);
                                overlay.addEventListener('click', e => {
                                    if (e.target === overlay) overlay.remove();
                                });
                                refreshInfo();
                            } else {
                                alert('A helper has already been applied today.');
                            }
                        });
                });

                list.appendChild(card);
            });
        })
        .catch(() => {});
});