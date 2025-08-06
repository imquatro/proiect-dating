document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('conversationList');
    const btn = document.getElementById('deleteConversationBtn');
    if (!list || !btn) return;

    let mode = 0; // 0: normal, 1: selecting, 2: ready to delete
    let selectedId = 0;
    let selectedLi = null;

    function reset() {
        mode = 0;
        selectedId = 0;
        selectedLi = null;
        btn.textContent = 'Delete conversation';
        list.classList.remove('select-mode');
        list.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
    }

    btn.addEventListener('click', () => {
        if (mode === 0) {
            mode = 1;
            btn.textContent = 'Select conversation';
            list.classList.add('select-mode');
        } else if (mode === 2 && selectedId) {
            fetch('messages_api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=delete_conversation&friend_id=${selectedId}`
            }).then(() => {
                if (selectedLi) {
                    selectedLi.remove();
                }
                if (!list.querySelector('li')) {
                    list.remove();
                    const p = document.createElement('p');
                    p.textContent = 'No conversations.';
                    btn.parentNode.insertBefore(p, btn);
                    btn.remove();
                } else {
                    reset();
                }
            });
        }
    });

    list.addEventListener('click', e => {
        if (mode !== 1) return;
        e.preventDefault();
        const li = e.target.closest('li');
        if (!li) return;
        selectedId = li.dataset.id;
        selectedLi = li;
        list.querySelectorAll('li').forEach(el => el.classList.remove('selected'));
        li.classList.add('selected');
        btn.textContent = 'Delete';
        mode = 2;
    });
});
