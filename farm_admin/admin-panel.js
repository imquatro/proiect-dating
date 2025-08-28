function initAdminPanel(panel){
    // tab switching
    const tabs = panel.querySelectorAll('.fa-tab-header button');
    const contents = panel.querySelectorAll('.fa-tab-content');
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const target = panel.querySelector(`#fa-tab-${btn.dataset.tab}`);
            if (target) target.classList.add('active');
        });
    });

    // add-item form behaviour
    const typeSel = panel.querySelector('select[name="item_type"]');
    if (typeSel) {
        const waterFields = panel.querySelectorAll('.water-field');
        const feedFields = panel.querySelectorAll('.feed-field');
        const updateFields = () => {
            if(typeSel.value === 'plant'){
                waterFields.forEach(el => el.style.display = 'block');
                feedFields.forEach(el => el.style.display = 'none');
            }else{
                waterFields.forEach(el => el.style.display = 'none');
                feedFields.forEach(el => el.style.display = 'block');
            }
        };
        typeSel.addEventListener('change', updateFields);
        updateFields();
    }

    // AJAX add item
    const addForm = panel.querySelector('#fa-item-form');
    if (addForm) {
        addForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(addForm);
            fetch('farm_admin/save_item.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = data.item;
                    const create = cls => {
                        const div = document.createElement('div');
                        div.className = cls;
                        div.dataset.id = item.id;
                        div.innerHTML = `<img src="${item.image_plant}" alt="${item.name}"><div class="qs-info"><span class="qs-price">${item.price}</span></div>`;
                        return div;
                    };
                    const editGrid = panel.querySelector('.fa-edit-grid');
                    const deleteGrid = panel.querySelector('.fa-delete-grid');
                    if (editGrid) editGrid.appendChild(create('fa-edit-item'));
                    if (deleteGrid) deleteGrid.appendChild(create('fa-delete-item'));

                    const qsPanel = document.getElementById('quickshop-panel');
                    if (qsPanel && qsPanel.dataset.slotType === item.slot_type) {
                        const qsGrid = qsPanel.querySelector('.quickshop-grid');
                        if (qsGrid) {
                            const qs = document.createElement('div');
                            qs.className = 'quickshop-item';
                            qs.dataset.itemId = item.id;
                            qs.dataset.price = item.price;
                            qs.dataset.water = item.water_interval;
                            qs.dataset.feed = item.feed_interval;
                            qs.dataset.waterTimes = item.water_times;
                            qs.dataset.feedTimes = item.feed_times;
                            qs.dataset.production = item.production;
                            qs.innerHTML = `<img src="${item.image_plant}" alt="${item.name}"><div class="qs-info"><span class="qs-price">${item.price}</span><button class="qs-buy">BUY/USE</button></div>`;
                            qsGrid.appendChild(qs);
                            if (typeof initQuickShop === 'function') {
                                initQuickShop(qsPanel);
                            }
                        }
                    }

                    addForm.reset();
                    panel.remove();
                }
            })
            .catch(err => console.error(err));
        });
    }

    initEditItems(panel);
    initDeleteItems(panel);

    const verBtn = panel.querySelector('#fa-update-version');
    const verDisplay = panel.querySelector('#fa-current-version');
    if (verBtn) {
        verBtn.addEventListener('click', () => {
            fetch('farm_admin/bump_version.php', {
                method: 'POST',
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (verDisplay) verDisplay.textContent = data.version;
                    alert('Version updated to ' + data.version);
                }
            });
        });
    }
}

function initDeleteItems(panel){
    const grid = panel.querySelector('#fa-tab-delete .fa-delete-grid');
    const delBtn = panel.querySelector('#fa-delete-item-btn');
    if (!grid || !delBtn) return;
    let selectedId = null;

    grid.addEventListener('click', e => {
        const it = e.target.closest('.fa-delete-item');
        if (!it) return;
        grid.querySelectorAll('.fa-delete-item').forEach(i => i.classList.remove('selected'));
        it.classList.add('selected');
        selectedId = it.dataset.id;
        delBtn.disabled = false;
    });

    delBtn.addEventListener('click', () => {
        if (!selectedId) return;
        fetch('farm_admin/delete_item.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: selectedId})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const delItem = grid.querySelector(`.fa-delete-item[data-id="${selectedId}"]`);
                if (delItem) delItem.remove();
                const editItem = panel.querySelector(`#fa-tab-edit .fa-edit-item[data-id="${selectedId}"]`);
                if (editItem) editItem.remove();
                const qsPanel = document.getElementById('quickshop-panel');
                if (qsPanel) {
                    const qsItem = qsPanel.querySelector(`.quickshop-item[data-item-id="${selectedId}"]`);
                    if (qsItem) qsItem.remove();
                }
                delBtn.disabled = true;
                selectedId = null;
            }
        })
        .catch(err => console.error(err));
    });
}

function initEditItems(panel){
    const grid = panel.querySelector('.fa-edit-grid');
    if (!grid) return;
    const form = panel.querySelector('#fa-edit-form');
    const typeSel = form.querySelector('select[name="item_type"]');
    const waterFields = form.querySelectorAll('.water-field');
    const feedFields = form.querySelectorAll('.feed-field');

    const toggleFields = () => {
        if (typeSel.value === 'plant') {
            waterFields.forEach(el => el.style.display = 'block');
            feedFields.forEach(el => el.style.display = 'none');
        } else {
            waterFields.forEach(el => el.style.display = 'none');
            feedFields.forEach(el => el.style.display = 'block');
        }
    };

    typeSel.addEventListener('change', toggleFields);

    grid.addEventListener('click', e => {
        const it = e.target.closest('.fa-edit-item');
        if (!it) return;
        grid.querySelectorAll('.fa-edit-item').forEach(i => i.classList.remove('selected'));
        it.classList.add('selected');
        const id = it.dataset.id;
        fetch(`farm_admin/get_item.php?id=${id}`)
            .then(res => res.json())
            .then(item => {
                form.style.display = 'block';
                form.querySelector('input[name="id"]').value = item.id;
                form.querySelector('input[name="name"]').value = item.name;
                typeSel.value = item.item_type;
                form.querySelector('select[name="slot_type"]').value = item.slot_type;
                form.querySelector('input[name="water_hours"]').value = Math.floor(item.water_interval / 3600);
                form.querySelector('input[name="water_minutes"]').value = Math.floor(item.water_interval % 3600 / 60);
                form.querySelector('input[name="water_seconds"]').value = item.water_interval % 60;
                form.querySelector('input[name="feed_hours"]').value = Math.floor(item.feed_interval / 3600);
                form.querySelector('input[name="feed_minutes"]').value = Math.floor(item.feed_interval % 3600 / 60);
                form.querySelector('input[name="feed_seconds"]').value = item.feed_interval % 60;
                form.querySelector('input[name="water_times"]').value = item.water_times;
                form.querySelector('input[name="feed_times"]').value = item.feed_times;
                form.querySelector('input[name="price"]').value = item.price;
                form.querySelector('input[name="sell_price"]').value = item.sell_price;
                form.querySelector('input[name="production"]').value = item.production;
                form.querySelector('input[name="image_name"]').value = item.image_plant.replace(/^img\//, '');
                form.querySelector('input[name="barn_capacity"]').value = item.barn_capacity;
                toggleFields();
            });
    });

    toggleFields();

    form.addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('farm_admin/update_item.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const item = data.item;
                const editItem = grid.querySelector(`.fa-edit-item[data-id="${item.id}"]`);
                if (editItem) {
                    editItem.querySelector('img').src = item.image_plant;
                    editItem.querySelector('.qs-price').textContent = item.price;
                }
                const delItem = panel.querySelector(`#fa-tab-delete .fa-delete-item[data-id="${item.id}"]`);
                if (delItem) {
                    delItem.querySelector('img').src = item.image_plant;
                    delItem.querySelector('.qs-price').textContent = item.price;
                }
                const qsPanel = document.getElementById('quickshop-panel');
                if (qsPanel) {
                    const qsItem = qsPanel.querySelector(`.quickshop-item[data-item-id="${item.id}"]`);
                    if (qsItem) {
                        qsItem.dataset.price = item.price;
                        qsItem.querySelector('img').src = item.image_plant;
                        qsItem.querySelector('.qs-price').textContent = item.price;
                    }
                }
                form.reset();
                form.style.display = 'none';
            }
        })
        .catch(err => console.error(err));
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('open-admin-panel');
    if (!btn) return;
    btn.addEventListener('click', () => {
        fetch('farm_admin/panel.php?ajax=1', { credentials: 'same-origin' })
            .then(res => res.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html.trim();
                const panel = temp.firstElementChild;
                if (!panel) {
                    alert(html.trim());
                    return;
                }
                panel.addEventListener('click', e => {
                    if (e.target === panel) {
                        panel.remove();
                    }
                });
                document.body.appendChild(panel);
                initAdminPanel(panel);
            });
    });
});
