(function () {
    let requestSent = window.requestPending || false;

    function showFriendRequestCard() {
        if (document.getElementById('nf-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'nf-overlay';

        const card = document.createElement('div');
        card.className = 'friend-request-card';

        const msg = document.createElement('div');
        card.appendChild(msg);

        if (requestSent) {
            msg.textContent = 'Wait for friend request response';
        } else {
            msg.textContent = 'You are not friends';

            const btn = document.createElement('button');
            btn.textContent = 'Add friend';
            btn.addEventListener('click', () => {
                btn.disabled = true;
                fetch('friend_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'send_request', user_id: window.visitId })
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            requestSent = true;
                            msg.textContent = 'Request sent';
                        } else {
                            msg.textContent = d.message || 'Error';
                        }
                        btn.remove();
                        setTimeout(() => {
                            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                        }, 1500);
                    })
                    .catch(() => {
                        msg.textContent = 'Error';
                        btn.remove();
                        setTimeout(() => {
                            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                        }, 1500);
                    });
            });
            card.appendChild(btn);
        }

        overlay.appendChild(card);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.parentNode.removeChild(overlay);
            }
        });
    }

    window.showFriendRequestCard = showFriendRequestCard;
})();