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

    // item type behaviour for all forms
    panel.querySelectorAll('form[data-item-form]').forEach(form => setupTypeSelect(form));

    initDeleteItems(panel);
    initEditItems(panel);
}

function setupTypeSelect(form){
    const typeSel = form.querySelector('select[name="item_type"]');
    if (!typeSel) return;
    const waterFields = form.querySelectorAll('.water-field');
    const feedFields = form.querySelectorAll('.feed-field');
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

function initDeleteItems(panel){
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
    const select = panel.querySelector('#fa-edit-select');
    const container = panel.querySelector('#fa-edit-form-container');
    const form = panel.querySelector('#fa-edit-form');
    if (!select || !container || !form) return;

    select.addEventListener('change', () => {
        const id = select.value;
        if (!id) {
            container.style.display = 'none';
            return;
        }
        fetch(`farm_admin/get_item.php?id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) return;
                container.style.display = 'block';
                form.querySelector('input[name="id"]').value = data.id;
                form.querySelector('input[name="name"]').value = data.name;
                form.querySelector('select[name="item_type"]').value = data.item_type;
                form.querySelector('select[name="slot_type"]').value = data.slot_type;
                form.querySelector('input[name="current_image_plant"]').value = data.image_plant;
                form.querySelector('input[name="current_image_ready"]').value = data.image_ready || '';
                form.querySelector('input[name="current_image_product"]').value = data.image_product;
                form.querySelector('input[name="barn_capacity"]').value = data.barn_capacity || 0;

                const wInt = parseInt(data.water_interval || 0);
                form.querySelector('input[name="water_hours"]').value = Math.floor(wInt / 3600);
                form.querySelector('input[name="water_minutes"]').value = Math.floor((wInt % 3600) / 60);
                form.querySelector('input[name="water_seconds"]').value = wInt % 60;

                const fInt = parseInt(data.feed_interval || 0);
                form.querySelector('input[name="feed_hours"]').value = Math.floor(fInt / 3600);
                form.querySelector('input[name="feed_minutes"]').value = Math.floor((fInt % 3600) / 60);
                form.querySelector('input[name="feed_seconds"]').value = fInt % 60;

                form.querySelector('input[name="water_times"]').value = data.water_times;
                form.querySelector('input[name="feed_times"]').value = data.feed_times;
                form.querySelector('input[name="price"]').value = data.price;
                form.querySelector('input[name="production"]').value = data.production;

                setupTypeSelect(form);
            });
    });
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