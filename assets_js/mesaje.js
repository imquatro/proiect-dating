document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.getElementById('typingIndicator');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const messageSound = new Audio('sounds/water-drop-plop.mp3');
    const typingSound = new Audio('sounds/typingssss.mp3');
    typingSound.loop = true;
    let typingSoundPlaying = false;
    let lastId = 0;
    let typingTimeout;
    let initialFetch = true;

    function formatRelativeTime(dateString) {
        const date = new Date(dateString.replace(' ', 'T'));
        const diffSeconds = Math.floor((Date.now() - date.getTime()) / 1000);
        if (diffSeconds < 60) return 'sent just now';
        const diffMinutes = Math.floor(diffSeconds / 60);
        if (diffMinutes < 60) {
            return `sent ${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`;
        }
        const diffHours = Math.floor(diffMinutes / 60);
        if (diffHours < 24) {
            return `sent ${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
        }
        const diffDays = Math.floor(diffHours / 24);
        return `sent ${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
    }

    function fetchMessages() {
        fetch(`messages_api.php?action=fetch&friend_id=${friendId}&last_id=${lastId}`)
            .then(r => r.json())
            .then(data => {
                if (Array.isArray(data.messages)) {
                    let newMessages = false;
                    let newMessagesFromFriend = false;
                    data.messages.forEach(m => {
                        const div = document.createElement('div');
                        div.className = 'chat-bubble' + (m.sender_id == currentUserId ? ' me' : '');
                        const msgSpan = document.createElement('span');
                        msgSpan.className = 'message-text';
                        msgSpan.textContent = m.message;
                        const timeSpan = document.createElement('span');
                        timeSpan.className = 'timestamp';
                        timeSpan.textContent = formatRelativeTime(m.created_at);
                        div.appendChild(msgSpan);
                        div.appendChild(timeSpan);
                        chatMessages.appendChild(div);
                        lastId = m.id;
                        newMessages = true;
                        if (m.sender_id != currentUserId) {
                            newMessagesFromFriend = true;
                        }
                    });
                    if (newMessagesFromFriend && !initialFetch) {
                        messageSound.currentTime = 0;
                        messageSound.play();
                    }
                    if (newMessages) {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                }
                typingIndicator.style.display = data.typing ? 'flex' : 'none';
                if (data.typing) {
                    if (!typingSoundPlaying) {
                        typingSound.currentTime = 0;
                        typingSound.play();
                        typingSoundPlaying = true;
                    }
                } else if (typingSoundPlaying) {
                    typingSound.pause();
                    typingSound.currentTime = 0;
                    typingSoundPlaying = false;
                }
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
        messageSound.currentTime = 0;
        messageSound.play();
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
            fetchMessages();
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
