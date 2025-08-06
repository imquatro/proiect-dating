document.addEventListener('DOMContentLoaded', () => {
    const indicator = document.getElementById('messageIndicator');
    if (!indicator) return;

    const sound = new Audio('sounds/water-drop-plop.mp3');
    let lastCount = 0;
    let firstCheck = true;

    function checkUnread() {
        fetch('messages_api.php?action=unread_count')
            .then(r => r.json())
            .then(data => {
                const count = parseInt(data.count, 10) || 0;
                if (count > 0) {
                    indicator.style.display = 'block';
                    if (!firstCheck && count > lastCount) {
                        sound.currentTime = 0;
                        sound.play().catch(() => {});
                    }
                } else {
                    indicator.style.display = 'none';
                }
                lastCount = count;
                firstCheck = false;
            })
            .catch(() => {});
    }

    checkUnread();
    setInterval(checkUnread, 3000);
});