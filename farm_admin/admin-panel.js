function initAdminPanel(panel){
    const typeSel = panel.querySelector('select[name="item_type"]');
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