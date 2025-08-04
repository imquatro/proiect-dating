document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.message-user').forEach(el => {
        el.addEventListener('click', function() {
            window.location.href = 'messages.php?user_id=' + this.dataset.userId;
        });
    });
    scrollToLastMessage();
    if (typeof selectedUserId !== 'undefined' && selectedUserId) {
        setInterval(fetchNewMessages, 3000);
    }
});

function scrollToLastMessage() {
    const convBody = document.getElementById('messagesConvBody');
    if (convBody) {
        convBody.scrollTop = convBody.scrollHeight;
    }
}

function fetchNewMessages(){
    fetch('fetch_new_messages.php?user_id=' + selectedUserId + '&last_id=' + lastMessageId)
        .then(r => r.json())
        .then(data => {
            data.messages.forEach(m => {
                appendMessage(m);
                lastMessageId = m.id;
            });
            if (data.messages && data.messages.length) {
                scrollToLastMessage();
            }
        })
        .catch(() => {});
}

function appendMessage(m){
    const convBody = document.getElementById('messagesConvBody');
    if (!convBody) return;
    const row = document.createElement('div');
    row.className = 'msg-row' + (m.sender_id == currentUserId ? ' own' : '');
    if (m.sender_id != currentUserId) {
        const img = document.createElement('img');
        img.src = m.avatar;
        img.className = 'msg-avatar';
        row.appendChild(img);
    }
    const bubble = document.createElement('div');
    bubble.className = 'msg-bubble' + (m.sender_id == currentUserId ? ' own' : '');
    bubble.textContent = m.message;
    row.appendChild(bubble);
    const time = document.createElement('span');
    time.className = 'msg-time';
    const d = new Date(m.created_at);
    time.textContent = d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
    row.appendChild(time);
    if (m.sender_id == currentUserId) {
        const img2 = document.createElement('img');
        img2.src = m.avatar;
        img2.className = 'msg-avatar';
        row.appendChild(img2);
    }
    convBody.appendChild(row);
}

window.updateContactUnread = function(details){
    const map = {};
    details.forEach(d => { map[d.sender_id] = d.cnt; });
    document.querySelectorAll('.message-user').forEach(el => {
        const uid = parseInt(el.dataset.userId, 10);
        if (map[uid] && (!selectedUserId || selectedUserId != uid)) {
            el.classList.add('has-unread');
        } else {
            el.classList.remove('has-unread');
        }
    });
};