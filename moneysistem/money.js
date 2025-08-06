document.addEventListener('DOMContentLoaded', () => {
    window.updateCurrency = function(money, gold) {
        const moneyEl = document.getElementById('moneyAmount');
        const goldEl = document.getElementById('goldAmount');
        if (moneyEl) moneyEl.textContent = money;
        if (goldEl) goldEl.textContent = gold;
    };
});