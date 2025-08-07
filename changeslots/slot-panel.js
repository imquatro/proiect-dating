document.addEventListener('DOMContentLoaded', () => {
    const content = document.querySelector('.content');
    if (content) {
        content.classList.add('no-scroll');
    }

    document.querySelectorAll('.cs-slot-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            alert('Functionality coming soon');
        });
    });
});