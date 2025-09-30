document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('helpersList');
    if (!list) return;

    function refreshInfo() {
        fetch('helper_info.php', { credentials: 'same-origin' })
            .then(r => r.json())
            .then(info => {
                list.querySelectorAll('.helper-card').forEach(card => {
                    const applyBtn = card.querySelector('.apply-helper-btn');
                    const statsDiv = card.querySelector('.helper-stats');
                    const baseWater = card.dataset.waters;
                    const baseFeed = card.dataset.feeds;
                    const baseHarvest = card.dataset.harvests;
                    if (info.helper && parseInt(card.dataset.id, 10) === info.helper.id) {
                        card.classList.add('selected');
                        statsDiv.innerHTML = `
                            <span>Water: ${info.waterUsed}/${info.waterLimit}</span>
                            <span>Feed: ${info.feedUsed}/${info.feedLimit}</span>
                            <span>Harvest: ${info.harvestUsed}/${info.harvestLimit}</span>
                        `;
                        applyBtn.textContent = 'Applied';
                        applyBtn.disabled = true;
                    } else {
                        card.classList.remove('selected');
                        statsDiv.innerHTML = `
                            <span>Water: ${baseWater}</span>
                            <span>Feed: ${baseFeed}</span>
                            <span>Harvest: ${baseHarvest}</span>
                        `;
                        applyBtn.textContent = 'Apply';
                        applyBtn.disabled = !!info.helper;
                    }
                });
            })
            .catch(() => {});
    }

    fetch('helpers_list.php', { credentials: 'same-origin' })
        .then(res => res.json())
        .then(data => {
            data.helpers.forEach(h => {
                const card = document.createElement('div');
                card.className = 'helper-card';
                card.dataset.id = h.id;
                card.dataset.waters = h.waters;
                card.dataset.feeds = h.feeds;
                card.dataset.harvests = h.harvests;
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
            refreshInfo();
        })
        .catch(() => {});
});