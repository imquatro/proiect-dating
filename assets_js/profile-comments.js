function initProfileComments(container) {
    const helperContainer = container.querySelector('#helper-avatars');
    const commentsList = container.querySelector('#comments-list');
    const commentForm = container.querySelector('#comment-form');
    const commentInput = container.querySelector('#comment-input');

    const fetchHelpers = () => {
        if (!helperContainer) return;
        let url = (window.baseUrl || '') + 'recent_helpers.php?limit=10';
        if (window.isVisitor && window.visitId) {
            url += '&user_id=' + window.visitId;
        }
        fetch(url, { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                helperContainer.innerHTML = '';
                if (!Array.isArray(data)) return;
                data.forEach(helper => {
                    const img = document.createElement('img');
                    img.src = helper.photo;
                    img.alt = helper.username;
                    img.dataset.id = helper.id;
                    let timer;
                    const openProfile = () => {
                        window.location.href = (window.baseUrl || '') + 'vizitfarm/vizitfarm.php?id=' + helper.id;
                    };
                    const clear = () => clearTimeout(timer);
                    img.addEventListener('mousedown', () => {
                        timer = setTimeout(openProfile, 1000);
                    });
                    img.addEventListener('touchstart', () => {
                        timer = setTimeout(openProfile, 1000);
                    });
                    ['mouseup', 'mouseleave'].forEach(ev => img.addEventListener(ev, clear));
                    ['touchend', 'touchcancel'].forEach(ev => img.addEventListener(ev, clear));
                    helperContainer.appendChild(img);
                });
            });
    };

    const renderComments = comments => {
        commentsList.innerHTML = '';
        comments.forEach(c => {
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.dataset.id = c.id;
            div.innerHTML = `<span class="author">${c.user}</span>: <span class="text">${c.text}</span><span class="delete">✖</span>`;
            const del = div.querySelector('.delete');
            del.addEventListener('click', () => {
                fetch('comments_api.php?id=' + c.id, { method: 'DELETE', credentials: 'same-origin' })
                    .then(() => loadComments());
            });
            commentsList.appendChild(div);
        });
    };

    const loadComments = () => {
        fetch('comments_api.php', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(renderComments);
    };

    if (commentForm) {
        commentForm.addEventListener('submit', e => {
            e.preventDefault();
            const text = commentInput.value.trim();
            if (!text) return;
            fetch('comments_api.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text })
            }).then(() => {
                commentInput.value = '';
                loadComments();
            });
        });
    }

    fetchHelpers();
    loadComments();
}

window.initProfileComments = initProfileComments;
