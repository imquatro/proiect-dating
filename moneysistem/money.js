document.addEventListener('DOMContentLoaded', () => {
    const moneyEl = document.getElementById('moneyAmount');
    const goldEl = document.getElementById('goldAmount');

    let currentMoney = parseInt(moneyEl?.textContent || '0', 10);
    let currentGold = parseInt(goldEl?.textContent || '0', 10);

    function animateValue(el, start, end) {
        const duration = 800;
        const startTime = performance.now();

        function frame(now) {
            const progress = Math.min((now - startTime) / duration, 1);
            const value = Math.round(start + (end - start) * progress);
            el.textContent = value;
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

    function refresh() {
        fetch('moneysistem/money_api.php')
            .then(r => r.json())
            .then(data => setValues(data.money, data.gold))
            .catch(() => {});
    }

    setInterval(refresh, 3000);
});