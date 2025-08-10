document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('open-settings-panel');
    const overlay = document.getElementById('settings-panel-overlay');
    const content = document.querySelector('.content');

    if (btn && overlay) {
        btn.addEventListener('click', () => {
            overlay.classList.add('active');
            if (content) {
                content.classList.add('no-scroll');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.classList.remove('active');
                if (content) {
                    content.classList.remove('no-scroll');
                }
            }
        });
    }
});
