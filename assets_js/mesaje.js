document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const messageSound = new Audio('sounds/water-drop-plop.mp3');
    let lastId = 0;
    let typingTimeout;
    let initialFetch = true;

    function fetchMessages() {
        fetch(`messages_api.php?action=fetch&friend_id=${friendId}&last_id=${lastId}`)
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data.messages)) {
                    data.messages.forEach(m => {
                        const div = document.createElement('div');
                        div.className = 'chat-bubble' + (m.sender_id == currentUserId ? ' me' : '');
                        div.textContent = m.message;
                        chatMessages.appendChild(div);
                        if (!initialFetch && m.sender_id != currentUserId) {
                            messageSound.currentTime = 0;
                            messageSound.play();
                        }
                        lastId = m.id;
                    });
                    if (data.messages.length) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }
                typingIndicator.style.display = data.typing ? 'flex' : 'none';
                if (initialFetch) {
                    initialFetch = false;
                }
            })
            .catch(() => {});
    }

    setInterval(fetchMessages, 1000);
    fetchMessages();

    messageForm.addEventListener('submit', e => {
        e.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;
        fetch('messages_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `action=send&friend_id=${friendId}&message=${encodeURIComponent(text)}`
        }).then(() => {
            messageInput.value = '';
            fetch('messages_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=stop_typing&friend_id=${friendId}`
            });
        });
    });

    messageInput.addEventListener('input', () => {
        clearTimeout(typingTimeout);
        const text = messageInput.value.trim();
        if (text !== '') {
            fetch('messages_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=typing&friend_id=${friendId}`
            });
        } else {
            fetch('messages_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=stop_typing&friend_id=${friendId}`
            });
        }
        typingTimeout = setTimeout(() => {}, 3000);
    });
});