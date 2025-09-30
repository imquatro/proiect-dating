document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('level-up-card');
    if (!card) return;
    let hideHandler;

    window.showLevelUp = function(level) {
        card.innerHTML = `<div class="level-number">${level}</div><div class="level-text">Great job! Keep it up!</div>`;
        card.style.display = 'flex';
        requestAnimationFrame(() => { card.style.opacity = '1'; });

        if (hideHandler) {
            document.removeEventListener('click', hideHandler);
        }

        hideHandler = function(e) {
            if (!card.contains(e.target)) {
                card.style.opacity = '0';
                setTimeout(() => { card.style.display = 'none'; }, 500);
                document.removeEventListener('click', hideHandler);
                hideHandler = null;
            }
        };

        document.addEventListener('click', hideHandler);
    };

    const stored = parseInt(localStorage.getItem('userLevel') || '0', 10);
    if (window.currentLevel && window.currentLevel > stored) {
        window.showLevelUp(window.currentLevel);
    }
    if (window.currentLevel) {
        localStorage.setItem('userLevel', window.currentLevel);
    }
});