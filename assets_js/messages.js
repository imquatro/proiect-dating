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
