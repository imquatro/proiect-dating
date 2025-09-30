function initProfileComments(container) {
    const helperContainer = container.querySelector('#helper-avatars');
    const commentsList = container.querySelector('#comments-list');
    const commentForm = container.querySelector('#comment-form');
    const commentInput = container.querySelector('#comment-input');
    const isVisitor = window.isVisitor || false;
    const visitId = window.visitId || null;
    const canInteract = typeof window.canInteract === 'undefined' ? true : window.canInteract;
    const datasetOwner = container && container.dataset && container.dataset.userId
        ? parseInt(container.dataset.userId, 10)
        : null;
    const profileOwnerId = (isVisitor && visitId)
        ? visitId
        : (window.profileOwnerId || datasetOwner || window.userId || null);
    const deleteAllowed = Boolean(window.userId && profileOwnerId && window.userId === profileOwnerId);

    const formatDate = (value) => {
        if (!value) return '';
        const parsed = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) {
            return value;
        }
        return parsed.toLocaleString();
    };

    const renderComments = (comments) => {
        if (!commentsList) return;
        commentsList.innerHTML = '';

        if (!Array.isArray(comments) || !comments.length) {
            const empty = document.createElement('div');
            empty.className = 'comment-empty';
            empty.textContent = 'Nu există comentarii încă.';
            commentsList.appendChild(empty);
            return;
        }

        comments.forEach(comment => {
            const item = document.createElement('div');
            item.className = 'comment-item';
            item.dataset.id = comment.id;

            const header = document.createElement('div');
            header.className = 'comment-header';

            const author = document.createElement('span');
            author.className = 'comment-author';
            author.textContent = comment.author || 'Utilizator';
            header.appendChild(author);

            const time = document.createElement('span');
            time.className = 'comment-time';
            time.textContent = formatDate(comment.created_at);
            header.appendChild(time);

            if (deleteAllowed && comment.can_delete) {
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'delete';
                delBtn.textContent = '×';
                delBtn.addEventListener('click', () => {
                    let url = 'comments_api.php?id=' + encodeURIComponent(comment.id);
                    if (isVisitor && visitId) url += '&user_id=' + encodeURIComponent(visitId);
                    fetch(url, {
                        method: 'DELETE',
                        credentials: 'same-origin'
                    }).then(res => {
                        if (!res.ok) throw new Error('delete_failed');
                        return res.json();
                    }).then(() => loadComments())
                    .catch(() => {});
                });
                header.appendChild(delBtn);
            }

            const body = document.createElement('div');
            body.className = 'comment-body';
            body.textContent = comment.text || '';

            item.appendChild(header);
            item.appendChild(body);
            commentsList.appendChild(item);
        });
    };

    const showHelperCard = (helper) => {
        const overlay = document.createElement('div');
        overlay.className = 'helper-card-overlay';
        let statusClass = 'status-offline';
        if (helper.status === 'online') statusClass = 'status-online';
        else if (helper.status === 'idle') statusClass = 'status-idle';
        let buttons = '';
        if (helper.isFriend) {
            buttons = `<a class="btn-farm" href="vizitfarm/vizitfarm.php?id=${helper.id}" title="Visit farm"><i class="fas fa-seedling"></i></a>` +
                `<button class="btn-view" data-id="${helper.id}" title="View profile"><i class="fas fa-eye"></i></button>` +
                `<button class="btn-msg" data-id="${helper.id}" title="Message"><i class="fas fa-envelope"></i></button>` +
                `<button class="btn-del" data-id="${helper.id}" title="Delete"><i class="fas fa-trash"></i></button>` +
                `<button class="btn-block" data-id="${helper.id}" title="Block"><i class="fas fa-ban"></i></button>`;
        } else {
            if (!helper.requestSent) {
                buttons += `<button class="btn-add" data-id="${helper.id}" title="Add friend"><i class="fas fa-user-plus"></i></button>`;
            }
            buttons += `<button class="btn-view" data-id="${helper.id}" title="View profile"><i class="fas fa-eye"></i></button>` +
                `<button class="btn-block" data-id="${helper.id}" title="Block"><i class="fas fa-ban"></i></button>`;
        }
        overlay.innerHTML = `<div class="user-card"><span class="status-dot ${statusClass}"></span><img src="${helper.photo}" class="user-card-avatar" alt=""><div class="user-card-text"><div class="user-card-name${helper.vip ? ' gold-shimmer' : ''}">${helper.username}</div></div><div class="user-card-buttons">${buttons}</div></div>`;
        container.appendChild(overlay);
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.remove(); });

        const btnAdd = overlay.querySelector('.btn-add');
        if (btnAdd) {
            btnAdd.addEventListener('click', () => {
                btnAdd.disabled = true;
                fetch('friend_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'send_request', user_id: helper.id })
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        btnAdd.remove();
                    } else {
                        btnAdd.disabled = false;
                    }
                });
            });
        }

        const btnDel = overlay.querySelector('.btn-del');
        if (btnDel) {
            btnDel.addEventListener('click', () => {
                btnDel.disabled = true;
                fetch('friend_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ action: 'remove_friend', user_id: helper.id })
                }).then(() => overlay.remove());
            });
        }

        const btnView = overlay.querySelector('.btn-view');
        if (btnView) btnView.addEventListener('click', () => {
            window.location.href = 'view_profile.php?user_id=' + helper.id;
        });
        const btnMsg = overlay.querySelector('.btn-msg');
        if (btnMsg) btnMsg.addEventListener('click', () => {
            window.location.href = 'mesaje.php?id=' + helper.id;
        });
        const btnFarm = overlay.querySelector('.btn-farm');
        if (btnFarm) btnFarm.addEventListener('click', () => {
            window.location.href = 'vizitfarm/vizitfarm.php?id=' + helper.id;
        });
        const btnBlock = overlay.querySelector('.btn-block');
        if (btnBlock) btnBlock.addEventListener('click', () => alert('Feature unavailable'));
    };

    const fetchHelpers = () => {
        if (!helperContainer) return;
        let url = (window.baseUrl || '') + 'recent_helpers.php?limit=10';
        if (isVisitor && visitId) {
            url += '&user_id=' + visitId;
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
                    let longPress = false;
                    let isDragging = false;
                    let startX;
                    let scrollStart;

                    const openProfile = () => {
                        longPress = true;
                        window.location.href = (window.baseUrl || '') + 'vizitfarm/vizitfarm.php?id=' + helper.id;
                    };

                    const onDown = (e) => {
                        longPress = false;
                        isDragging = false;
                        const point = e.touches ? e.touches[0] : e;
                        startX = point.pageX;
                        scrollStart = helperContainer.scrollLeft;
                        timer = setTimeout(openProfile, 500);

                        const onMove = (ev) => {
                            const p = ev.touches ? ev.touches[0] : ev;
                            const walk = p.pageX - startX;
                            if (Math.abs(walk) > 5) {
                                isDragging = true;
                                helperContainer.scrollLeft = scrollStart - walk;
                                clearTimeout(timer);
                                ev.preventDefault();
                            }
                        };

                        const onUp = () => {
                            clearTimeout(timer);
                            document.removeEventListener('mousemove', onMove);
                            document.removeEventListener('touchmove', onMove);
                            document.removeEventListener('mouseup', onUp);
                            document.removeEventListener('touchend', onUp);
                            document.removeEventListener('touchcancel', onUp);
                            if (isDragging || longPress) return;
                            showHelperCard(helper);
                        };

                        document.addEventListener('mousemove', onMove);
                        document.addEventListener('touchmove', onMove, { passive: false });
                        document.addEventListener('mouseup', onUp);
                        document.addEventListener('touchend', onUp);
                        document.addEventListener('touchcancel', onUp);
                    };

                    img.addEventListener('mousedown', onDown);
                    img.addEventListener('touchstart', onDown);
                    helperContainer.appendChild(img);
                });
            });
    };


    const loadComments = () => {
        let url = 'comments_api.php';
        if (isVisitor && visitId) url += '?user_id=' + visitId;
        fetch(url, { credentials: 'same-origin' })
            .then(res => {
                if (!res.ok) throw new Error('load_failed');
                return res.json();
            })
            .then(renderComments)
            .catch(() => renderComments([]));
    };

    if (commentForm) {
        const submitBtn = commentForm.querySelector('button');
        if (isVisitor && !canInteract) {
            if (!container.querySelector('.comment-locked')) {
                const lockMsg = document.createElement('div');
                lockMsg.className = 'comment-locked';
                lockMsg.textContent = 'Trebuie să fiți prieteni pentru a lăsa un comentariu.';
                commentForm.parentNode.insertBefore(lockMsg, commentForm);
            }
            if (submitBtn) submitBtn.disabled = true;
            if (commentInput) {
                commentInput.disabled = true;
                commentInput.placeholder = 'Comentariile sunt disponibile doar prietenilor.';
            }
            commentForm.classList.add('disabled');
        }
        commentForm.addEventListener('submit', e => {
            e.preventDefault();
            if (isVisitor && !canInteract) return;
            if (!commentInput) return;
            const text = commentInput.value.trim();
            if (!text) return;
            let url = 'comments_api.php';
            if (isVisitor && visitId) url += '?user_id=' + visitId;
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text })
            }).then(res => {
                if (!res.ok) throw new Error('submit_failed');
                return res.json();
            }).then(() => {
                commentInput.value = '';
                loadComments();
            }).catch(() => {});
        });
    }


    fetchHelpers();
    loadComments();
}

window.initProfileComments = initProfileComments;
