// Polls for pending friend requests and shows indicators
// on the friends nav icon and Friends Requests tab button.

document.addEventListener('DOMContentLoaded', () => {
    const navIndicator = document.getElementById('friendIndicator');
    const tabIndicator = document.getElementById('friendRequestsIndicator');

    function update(count) {
        const show = count > 0;
        if (navIndicator) navIndicator.style.display = show ? 'block' : 'none';
        if (tabIndicator) tabIndicator.style.display = show ? 'block' : 'none';
    }

    window.updateFriendRequestIndicators = update;

    function check() {
        fetch('friend_requests_api.php')
            .then(r => r.json())
            .then(d => {
                const c = parseInt(d.count, 10) || 0;
                update(c);
            })
            .catch(() => {});
    }

    check();
    setInterval(check, 3000);
});