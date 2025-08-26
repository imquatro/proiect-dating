document.addEventListener('DOMContentLoaded', () => {
    const moneyEl = document.getElementById('moneyAmount');
    const goldEl = document.getElementById('goldAmount');

    let currentMoney = parseInt(moneyEl?.textContent.replace(/\./g, '') || '0', 10);
    let currentGold = parseInt(goldEl?.textContent.replace(/\./g, '') || '0', 10);

    function formatNumber(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function animateValue(el, start, end) {
        const duration = 800;
        const startTime = performance.now();

        function frame(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const value = Math.round(start + (end - start) * progress);
            el.textContent = formatNumber(value);
            if (progress < 1) {
                requestAnimationFrame(frame);
            }
        }

        requestAnimationFrame(frame);
    }

    function setValues(money, gold) {
        if (typeof money === 'number' && !isNaN(money) && money !== currentMoney) {
            animateValue(moneyEl, currentMoney, money);
            currentMoney = money;
        }
        if (typeof gold === 'number' && !isNaN(gold) && gold !== currentGold) {
            animateValue(goldEl, currentGold, gold);
            currentGold = gold;
        }
    }

    window.updateCurrency = setValues;

    const originalFetch = window.fetch;
    window.fetch = function (...args) {
        return originalFetch(...args).then(res => {
            try {
                const clone = res.clone();
                const ct = res.headers.get('content-type') || '';
                if (ct.includes('application/json')) {
                    clone.json().then(data => {
                        if (data && (data.money !== undefined || data.gold !== undefined)) {
                            setValues(data.money, data.gold);
                        }
                    }).catch(() => {});
                }
            } catch (e) {}
            return res;
        });
    };

    function refresh() {
        fetch('moneysistem/money_api.php').catch(() => {});
    }

    setInterval(refresh, 3000);
});