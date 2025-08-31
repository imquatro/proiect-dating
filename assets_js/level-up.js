document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('level-up-card');
    if (!card) return;
    window.showLevelUp = function(level) {
        card.innerHTML = `<div class="level-number">${level}</div><div class="level-text">Great job! Keep it up!</div>`;
        card.style.display = 'flex';
        requestAnimationFrame(() => { card.style.opacity = '1'; });
        setTimeout(() => {
            card.style.opacity = '0';
            setTimeout(() => { card.style.display = 'none'; }, 500);
        }, 2000);
    };
    const stored = parseInt(localStorage.getItem('userLevel') || '0', 10);
    if (window.currentLevel && window.currentLevel > stored) {
        window.showLevelUp(window.currentLevel);
    }
    if (window.currentLevel) {
        localStorage.setItem('userLevel', window.currentLevel);
    }
});
