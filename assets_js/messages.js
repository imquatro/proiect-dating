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

// Ștergere conversație
const deleteBtn = document.getElementById('deleteConvBtn');
if (deleteBtn) {
    const modal = document.getElementById('deleteModal');
    const cancelBtn = document.getElementById('deleteCancel');
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
