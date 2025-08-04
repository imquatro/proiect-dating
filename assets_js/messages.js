document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.message-user').forEach(el => {
        el.addEventListener('click', function() {
            window.location.href = 'messages.php?user_id=' + this.dataset.userId;
        });
    });
    scrollToLastMessage();
});

function scrollToLastMessage() {
    const convBody = document.getElementById('messagesConvBody');
    if (convBody) {
        convBody.scrollTop = convBody.scrollHeight;
    }
}