document.addEventListener('DOMContentLoaded', function () {
    const panel = document.getElementById('shopPanel');
    const tabButtons = panel.querySelectorAll('.tab-btn');
    const tabContents = panel.querySelectorAll('.tab-content');

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabButtons.forEach(function (b) { b.classList.remove('active'); });
            tabContents.forEach(function (c) { c.classList.remove('active'); });
            btn.classList.add('active');
            const target = btn.getAttribute('data-tab');
            const content = panel.querySelector('#' + target);
            if (content) {
                content.classList.add('active');
            }
        });
    });
});