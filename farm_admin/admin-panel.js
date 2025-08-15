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

    initEditItems(panel);
    initManageItems(panel);
}

function initManageItems(panel){
    const select = panel.querySelector('#fa-item-select');
    if (!select) return;
    const img = panel.querySelector('#fa-item-image');
    const delBtn = panel.querySelector('#fa-delete-item');

    select.addEventListener('change', () => {
        const opt = select.selectedOptions[0];
        if (opt && opt.value) {
            img.src = opt.dataset.image;
            img.style.display = 'block';
            delBtn.disabled = false;
        } else {
            img.style.display = 'none';
            delBtn.disabled = true;
        }
    });

    delBtn.addEventListener('click', () => {
        const id = select.value;
        if (!id) return;
        fetch('farm_admin/delete_item.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                select.querySelector(`option[value="${id}"]`).remove();
                img.style.display = 'none';
                delBtn.disabled = true;
            }
        });
    });
}

function initEditItems(panel){
    const items = panel.querySelectorAll('.fa-edit-item');
    if (!items.length) return;
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

    items.forEach(it => {
        it.addEventListener('click', () => {
            const id = it.dataset.id;
            items.forEach(i => i.classList.remove('selected'));
            it.classList.add('selected');
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
                    form.querySelector('input[name="production"]').value = item.production;
                    form.querySelector('input[name="current_image_plant"]').value = item.image_plant;
                    form.querySelector('input[name="current_image_product"]').value = item.image_product;
                    form.querySelector('input[name="barn_capacity"]').value = item.barn_capacity;
                    toggleFields();
                });
        });
    });

    toggleFields();
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('open-admin-panel');
    if (!btn) return;
    btn.addEventListener('click', () => {
        fetch('farm_admin/panel.php?ajax=1')
            .then(res => res.text())
            .then(html => {
                const temp = document.createElement('div');
                temp.innerHTML = html.trim();
                const panel = temp.firstElementChild;
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