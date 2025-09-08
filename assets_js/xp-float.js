(function() {
    window.showFloatingText = function(target, opts = {}) {
        if (!target) return;
        const rect = target.getBoundingClientRect();
        const div = document.createElement('div');
        div.className = 'xp-float';
        let html = '';
        if (opts.money) {
            html += `<div class="money"><img src="img/gold.png" alt="">+${opts.money}</div>`;
        }
            html += `<div class="xp">+${opts.xp} XP</div>`;
        }
        div.innerHTML = html;
        div.style.left = `${rect.left + rect.width / 2}px`;
        div.style.top = `${rect.top}px`;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    };
})();
