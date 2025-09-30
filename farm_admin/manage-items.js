function initManageItems(panel){
    const select = panel.querySelector('#fa-item-select');
    const img = panel.querySelector('#fa-item-image');
    const delBtn = panel.querySelector('#fa-delete-item');
    const imgPrefix = panel.dataset.prefix || '';

    select.addEventListener('change', () => {
        const opt = select.selectedOptions[0];
        if (opt && opt.value) {
            img.src = imgPrefix + opt.dataset.image;
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
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({id})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                select.querySelector(`option[value="${id}"]`).remove();
                select.value = '';
                img.style.display = 'none';
                delBtn.disabled = true;

                const adminPanel = document.getElementById('fa-admin-panel');
                if (adminPanel) {
                    const delItem = adminPanel.querySelector(`#fa-tab-delete .fa-delete-item[data-id="${id}"]`);
                    if (delItem) delItem.remove();
                    const editItem = adminPanel.querySelector(`#fa-tab-edit .fa-edit-item[data-id="${id}"]`);
                    if (editItem) editItem.remove();
                }

                const qsPanel = document.getElementById('quickshop-panel');
                if (qsPanel) {
                    const qsItem = qsPanel.querySelector(`.quickshop-item[data-item-id="${id}"]`);
                    if (qsItem) qsItem.remove();
                }
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('open-manage-items');
    if (!btn) return;
    btn.addEventListener('click', () => {
        fetch('farm_admin/manage_items.php?ajax=1')
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
                initManageItems(panel);
            });
    });
});
