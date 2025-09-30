function normalizeImg(path){
    if(!path) return '';
    path = path.replace(/^\/+/,'');
    return path.startsWith('img/') ? path : 'img/' + path;
}

function initAdminPanel(panel){
    const imgPrefix = panel.dataset.prefix || '';
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
                        div.innerHTML = `<img src="${imgPrefix}${item.image_plant}" alt="${item.name}"><div class="qs-info"><span class="qs-price">${item.price}</span></div>`;
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
                            const qsPrefix = qsPanel.dataset.prefix || '';
                            const qsImg = normalizeImg(item.image_plant);
                            const qs = document.createElement('div');
                            qs.className = 'quickshop-item';
                            qs.dataset.itemId = item.id;
                            qs.dataset.price = item.price;
                            qs.dataset.water = item.water_interval;
                            qs.dataset.feed = item.feed_interval;
                            qs.dataset.waterTimes = item.water_times;
                            qs.dataset.feedTimes = item.feed_times;
                            qs.dataset.production = item.production;
                            qs.innerHTML = `<img src="${qsPrefix}${qsImg}" alt="${item.name}"><div class="qs-info"><span class="qs-price">${item.price}</span><button class="qs-buy">BUY/USE</button></div>`;
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

    const vipForm = panel.querySelector('#fa-vip-form');
    if (vipForm) {
        vipForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(vipForm);
            fetch('farm_admin/save_vip.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('VIP item added');
                    vipForm.reset();
                }
            })
            .catch(err => console.error(err));
        });
    }

    const achForm = panel.querySelector('#fa-achievement-form');
    if (achForm) {
        achForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(achForm);
            fetch('farm_admin/save_achievement.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Achievement added');
                    const idInput = achForm.querySelector('input[name="id"]');
                    const next = parseInt(idInput.value, 10) + 1;
                    achForm.reset();
                    idInput.value = next;
                    idInput.defaultValue = next;
                }
            })
            .catch(err => console.error(err));
        });
    }

    if (typeof initAchievementDelete === 'function') {
        initAchievementDelete(panel);
    }

    panel.querySelectorAll('.fa-delete-vip-form').forEach(delVipForm => {
        delVipForm.addEventListener('submit', e => {
            e.preventDefault();
            const formData = new FormData(delVipForm);
            fetch('farm_admin/delete_vip.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('VIP item deleted');
                    const select = delVipForm.querySelector('select[name="vip_name"]');
                    const val = formData.get('vip_name');
                    const opt = select.querySelector(`option[value="${val}"]`);
                    if (opt) opt.remove();
                }
            })
            .catch(err => console.error(err));
        });
    });
    const versionBtn = panel.querySelector('#fa-update-version');
    if (versionBtn) {
        versionBtn.addEventListener('click', () => {
            fetch('farm_admin/bump_version.php', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const span = panel.querySelector('#fa-current-version');
                        if (span) span.textContent = data.version;
                    }
                })
                .catch(err => console.error(err));
        });
    }
    initDeleteItems(panel);
    initEditItems(panel);
    initEditHelpers(panel);
}

function initEditItems(panel){
    const grid = panel.querySelector('.fa-edit-grid');
    if (!grid) return;
    const form = panel.querySelector('#fa-edit-form');
    const typeSel = form.querySelector('select[name="item_type"]');
    const imgPrefix = panel.dataset.prefix || '';
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
        let it = e.target;
        while (it && it !== grid && !it.classList.contains('fa-edit-item')) {
            it = it.parentElement;
        }
        if (!it || it === grid) return;
        const items = grid.querySelectorAll('.fa-edit-item');
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('selected');
        }
        it.classList.add('selected');
        const id = it.dataset.id;
        fetch(`farm_admin/get_item.php?id=${id}`, { credentials: 'same-origin' })
            .then(res => {
                if (!res.ok) return res.json().then(err => Promise.reject(err.error || 'Error'));
                return res.json();
            })
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
                form.querySelector('input[name="image_name"]').value = normalizeImg(item.image_plant).replace(/^img\//, '');
                form.querySelector('input[name="barn_capacity"]').value = item.barn_capacity;
                toggleFields();
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                const nameInput = form.querySelector('input[name="name"]');
                if (nameInput) nameInput.focus();
            })
            .catch(err => console.error(err));
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
        .then(res => {
            if (!res.ok) return res.json().then(err => Promise.reject(err.error || 'Error'));
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const item = data.item;
                const editItem = grid.querySelector(`.fa-edit-item[data-id="${item.id}"]`);
                if (editItem) {
                    editItem.querySelector('img').src = imgPrefix + normalizeImg(item.image_plant);
                    editItem.querySelector('.qs-price').textContent = item.price;
                }
                const delItem = panel.querySelector(`#fa-tab-delete .fa-delete-item[data-id="${item.id}"]`);
                if (delItem) {
                    delItem.querySelector('img').src = imgPrefix + normalizeImg(item.image_plant);
                    delItem.querySelector('.qs-price').textContent = item.price;
                }
                 const qsPanel = document.getElementById('quickshop-panel');
                if (qsPanel) {
                    const qsItem = qsPanel.querySelector(`.quickshop-item[data-item-id="${item.id}"]`);
                    if (qsItem) {
                        const qsPrefix = qsPanel.dataset.prefix || '';
                        qsItem.dataset.price = item.price;
                        qsItem.querySelector('img').src = qsPrefix + normalizeImg(item.image_plant);
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

function initEditHelpers(panel){
    const grid = panel.querySelector('.fa-edit-helper-grid');
    if (!grid) return;
    const form = panel.querySelector('#fa-helper-edit-form');
    const imgPrefix = panel.dataset.prefix || '';
    const helperImg = name => {
        if (!name) return '';
        name = name.replace(/^img\//, '');
        if (!/\.(png|gif|jpe?g)$/i.test(name)) {
            name += '.png';
        }
        return imgPrefix + 'img/' + name;
    };

    grid.querySelectorAll('.fa-helper-item').forEach(item => {
        item.addEventListener('click', () => {
            grid.querySelectorAll('.fa-helper-item').forEach(i => i.classList.remove('selected'));
            item.classList.add('selected');
            form.style.display = 'block';
            form.querySelector('input[name="id"]').value = item.dataset.id;
            form.querySelector('input[name="name"]').value = item.dataset.name;
            form.querySelector('input[name="image"]').value = item.dataset.image;
            form.querySelector('input[name="message_file"]').value = item.dataset.message;
            form.querySelector('input[name="waters"]').value = item.dataset.waters || 0;
            form.querySelector('input[name="feeds"]').value = item.dataset.feeds || 0;
            form.querySelector('input[name="harvests"]').value = item.dataset.harvests || 0;
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            const nameInput = form.querySelector('input[name="name"]');
            if (nameInput) nameInput.focus();
        });
    });
    const addForm = panel.querySelector('#fa-helper-form');
    if (addForm) {
        addForm.addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(addForm);
            fetch('farm_admin/save_helper.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const h = data.helper;
                        const div = document.createElement('div');
                        div.className = 'fa-helper-item';
                        div.dataset.id = h.id;
                        div.dataset.name = h.name;
                        div.dataset.image = h.image;
                        div.dataset.message = h.message_file;
                        div.dataset.waters = h.waters;
                        div.dataset.feeds = h.feeds;
                        div.dataset.harvests = h.harvests;
                        div.innerHTML = `<img src="${helperImg(h.image)}" alt="${h.name}"><span>${h.name}</span>`;
                        grid.appendChild(div);
                        addForm.reset();
                        initEditHelpers(panel);
                    }
                });
        });
    }

    if (form) {
        form.addEventListener('submit', e => {
            e.preventDefault();
            const fd = new FormData(form);
            fetch('farm_admin/update_helper.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const item = grid.querySelector(`.fa-helper-item[data-id="${fd.get('id')}"]`);
                        if (item) {
                            item.dataset.name = fd.get('name');
                            item.dataset.image = fd.get('image');
                            item.dataset.message = fd.get('message_file');
                            item.dataset.waters = fd.get('waters');
                            item.dataset.feeds = fd.get('feeds');
                            item.dataset.harvests = fd.get('harvests');
                            item.querySelector('img').src = helperImg(fd.get('image'));
                            item.querySelector('span').textContent = fd.get('name');
                        }
                        form.style.display = 'none';
                    }
                });
        });
    }
}

function initDeleteItems(panel){
    const grid = panel.querySelector('.fa-delete-grid');
    const btn = panel.querySelector('#fa-delete-item-btn');
    if (!grid || !btn) return;

    let selectedId = null;

    grid.addEventListener('click', e => {
        let it = e.target;
        while (it && it !== grid && !it.classList.contains('fa-delete-item')) {
            it = it.parentElement;
        }
        if (!it || it === grid) return;
        const items = grid.querySelectorAll('.fa-delete-item');
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('selected');
        }
        it.classList.add('selected');
        selectedId = it.dataset.id;
        btn.disabled = false;
    });

    btn.addEventListener('click', () => {
        if (!selectedId) return;
        fetch('farm_admin/delete_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id: selectedId }),
            credentials: 'same-origin'
        })
        .then(res => {
            if (!res.ok) return res.json().then(err => Promise.reject(err.error || 'Error'));
            return res.json();
        })
        .then(data => {
            if (data.success) {
                const item = grid.querySelector(`.fa-delete-item[data-id="${selectedId}"]`);
                if (item) item.remove();
                btn.disabled = true;
                selectedId = null;
            }
        })
        .catch(err => console.error(err));
    });
}

window.initAdminPanel = initAdminPanel;
window.initEditItems = initEditItems;
window.initDeleteItems = initDeleteItems;

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('open-admin-panel');
    if (btn) {
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
    }
    const panel = document.getElementById('fa-admin-panel');
    if (panel) {
        panel.addEventListener('click', e => {
            if (e.target === panel) {
                panel.remove();
            }
        });
        initAdminPanel(panel);
    }
});