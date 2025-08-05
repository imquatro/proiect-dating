// Deschide conversația pentru utilizatorul selectat
document.querySelectorAll('.message-user').forEach(el => {
    el.addEventListener('click', function() {
        const uid = this.dataset.userId;
        if (uid) {
            window.location.href = 'messages.php?user_id=' + uid;
        }
    });
});

// Scroll la ultimul mesaj din conversație
function scrollToLastMessage() {
    const convBody = document.querySelector('.messages-conv-body');
    if (convBody) {
        convBody.scrollTop = convBody.scrollHeight;
    }
}

// Apelează după ce încarci sau adaugi mesaje noi
scrollToLastMessage();

const convBody = document.getElementById('messagesConvBody');
if (convBody) {
    const currentId = convBody.dataset.currentId;
    const otherInput = document.querySelector('input[name="user_id"]');
    const otherId = otherInput ? otherInput.value : null;

    const messageForm = document.querySelector('.messages-conv-footer');
    if (messageForm && otherId) {
        messageForm.addEventListener('submit', e => {
            e.preventDefault();
            const msgField = messageForm.querySelector('input[name="message"]');
            const text = msgField.value.trim();
            if (!text) return;
            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'user_id=' + encodeURIComponent(otherId) + '&message=' + encodeURIComponent(text)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                msgField.value = '';
                const msg = data.message;
                const row = document.createElement('div');
                row.className = 'msg-row own';
                row.dataset.id = msg.id;
                const bubble = document.createElement('div');
                bubble.className = 'msg-bubble own';
                bubble.textContent = msg.message;
                row.appendChild(bubble);
                const span = document.createElement('span');
                span.className = 'msg-time';
                span.textContent = new Date(msg.created_at).toLocaleTimeString('ro-RO', {hour:'2-digit', minute:'2-digit'});
                row.appendChild(span);
                const img = document.createElement('img');
                img.src = msg.avatar;
                img.alt = '';
                img.className = 'msg-avatar';
                row.appendChild(img);
                convBody.appendChild(row);
                scrollToLastMessage();
            });
        });
    }
	
    function fetchNewMessages() {
        if (!otherId) return;
        let lastId = 0;
        const lastRow = convBody.querySelector('.msg-row:last-child');
        if (lastRow) {
            lastId = lastRow.dataset.id || 0;
        }
        fetch(`fetch_new_messages.php?user_id=${encodeURIComponent(otherId)}&last_id=${encodeURIComponent(lastId)}`)
            .then(r => r.json())
            .then(data => {
                if (!data.messages || !data.messages.length) return;
                data.messages.forEach(msg => {
                    const row = document.createElement('div');
                    row.className = 'msg-row' + (msg.sender_id == currentId ? ' own' : '');
                    row.dataset.id = msg.id;
                    if (msg.sender_id != currentId) {
                        const img = document.createElement('img');
                        img.src = msg.avatar;
                        img.alt = '';
                        img.className = 'msg-avatar';
                        row.appendChild(img);
                    }
                    const bubble = document.createElement('div');
                    bubble.className = 'msg-bubble' + (msg.sender_id == currentId ? ' own' : '');
                    bubble.textContent = msg.message;
                    row.appendChild(bubble);
                    const span = document.createElement('span');
                    span.className = 'msg-time';
                    span.textContent = new Date(msg.created_at).toLocaleTimeString('ro-RO', {hour:'2-digit', minute:'2-digit'});
                    row.appendChild(span);
                    if (msg.sender_id == currentId) {
                        const img = document.createElement('img');
                        img.src = msg.avatar;
                        img.alt = '';
                        img.className = 'msg-avatar';
                        row.appendChild(img);
                    }
                    convBody.appendChild(row);
                    scrollToLastMessage();
                });
            });
    }
    setInterval(fetchNewMessages, 3000);
}

// Ștergere conversație
const deleteBtn = document.getElementById('deleteConvBtn');
if (deleteBtn) {
    const modal      = document.getElementById('deleteModal');
    const cancelBtn  = document.getElementById('deleteCancel');
    const confirmBtn = document.getElementById('deleteConfirm');
    deleteBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
    });
    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    confirmBtn.addEventListener('click', () => {
        const uid = document.querySelector('input[name="user_id"]').value;
        fetch('delete_conversation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'user_id=' + encodeURIComponent(uid)
        })
        .then(r => r.json())
        .then(data => {
            modal.style.display = 'none';
            if (data.success) {
                window.location.href = 'messages.php';
            }
        });
    });
}
