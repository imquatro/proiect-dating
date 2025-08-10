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
            });
    });
});