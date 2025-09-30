(function() {
    window.showFloatingText = function(target, opts = {}) {
        if (!target) return;
        const rect = target.getBoundingClientRect();
        const div = document.createElement('div');
        div.className = 'xp-float';
        let html = '';
        if (opts.money) {
            html += `<div class="money">+${opts.money}</div>`;
        }
        if (opts.xp) {
            html += `<div class="xp">+${opts.xp} XP</div>`;
        }
        div.innerHTML = html;
        div.style.left = `${rect.left + rect.width / 2}px`;
        div.style.top = `${rect.top}px`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    };
})();
