(function () {
    var cardContainer = document.getElementById('cardContainer');
    var searchInput = document.getElementById('searchInput');
    var searchBtn = document.getElementById('searchBtn');
    var tabButtons = document.querySelectorAll('.tab-btn');
    var contentEl = document.querySelector('.content');

    var currentTab = 'online';
    var visibleCount = 10;
    var online = Array.isArray(window.onlineUsers) ? window.onlineUsers : [];
    var requests = Array.isArray(window.friendRequests) ? window.friendRequests : [];
    var friends = Array.isArray(window.friends) ? window.friends : [];
    var currentList = online.slice();

    function statusClass(status) {
        if (status === 'online') return 'status-online';
        if (status === 'idle') return 'status-idle';
        return 'status-offline';
    }

    function buttonsHtml(tab, id, requested, isFriend) {
        if (tab === 'online') {
            if (isFriend) {
                return '<button class="btn-farm" data-id="' + id + '" title="Visit farm"><i class="fas fa-seedling"></i></button>' +
                    '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                    '<button class="btn-msg" data-id="' + id + '" title="Message"><i class="fas fa-envelope"></i></button>' +
                    '<button class="btn-del" data-id="' + id + '" title="Delete"><i class="fas fa-trash"></i></button>' +
                    '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
            }
            var html = '';
            if (!requested) {
                html += '<button class="btn-add" data-id="' + id + '" title="Add friend"><i class="fas fa-user-plus"></i></button>';
            }
            html += '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>';
            html += '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
            return html;
        }
        if (tab === 'requests') {
            return '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                '<button class="btn-accept" data-id="' + id + '" title="Accept"><i class="fas fa-check"></i></button>' +
                '<button class="btn-refuse" data-id="' + id + '" title="Decline"><i class="fas fa-times"></i></button>' +
                '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
        }
        if (tab === 'friends') {
            return '<button class="btn-farm" data-id="' + id + '" title="Visit farm"><i class="fas fa-seedling"></i></button>' +
                '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                '<button class="btn-msg" data-id="' + id + '" title="Message"><i class="fas fa-envelope"></i></button>' +
                '<button class="btn-del" data-id="' + id + '" title="Delete"><i class="fas fa-trash"></i></button>' +
                '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
        }
        return '';
    }

    function render() {
        cardContainer.innerHTML = '';
        var list = currentList.slice(0, visibleCount);
        for (var i = 0; i < list.length; i++) {
            var u = list[i];
            var div = document.createElement('div');
            div.className = 'user-card';
            div.innerHTML =
                '<span class="status-dot ' + statusClass(u.status) + '"></span>' +
                '<img src="' + u.avatar + '" class="user-card-avatar" alt="">' +
                '<div class="user-card-name">' + u.username + '</div>' +
                '<div class="user-card-buttons">' + buttonsHtml(currentTab, u.id, u.requestSent, u.isFriend) + '</div>';
            cardContainer.appendChild(div);
        }
    }

    function setTab(tab) {
        currentTab = tab;
        for (var i = 0; i < tabButtons.length; i++) {
            var btn = tabButtons[i];
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
        }
        searchInput.value = '';
        visibleCount = 10;
        if (tab === 'online') {
            currentList = online.slice();
            searchInput.disabled = false;
        } else if (tab === 'requests') {
            currentList = requests.slice();
            searchInput.disabled = true;
        } else {
            currentList = friends.slice();
            searchInput.disabled = true;
        }
        render();
    }

    for (var i = 0; i < tabButtons.length; i++) {
        tabButtons[i].addEventListener('click', function () {
            setTab(this.getAttribute('data-tab'));
        });
    }

    searchInput.addEventListener('input', function () {
        if (currentTab !== 'online') return;
        var val = searchInput.value.toLowerCase();
        if (!val) {
            currentList = online.slice();
        } else {
            currentList = online.filter(function (u) {
                return u.username.toLowerCase().indexOf(val) !== -1;
            });
        }
        visibleCount = currentList.length;
        render();
    });

    searchBtn.addEventListener('click', function () {
        var ev = document.createEvent('Event');
        ev.initEvent('input', true, true);
        searchInput.dispatchEvent(ev);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !cardContainer.contains(e.target)) {
            if (searchInput.value) {
                searchInput.value = '';
                currentList = online.slice();
                visibleCount = 10;
                render();
            }
        }
    });

    contentEl.addEventListener('scroll', function () {
        if (contentEl.scrollTop + contentEl.clientHeight >= contentEl.scrollHeight - 5) {
            if (visibleCount < currentList.length) {
                visibleCount += 5;
                render();
            }
        }
    });

    cardContainer.addEventListener('click', function (e) {
        var target = e.target;
        while (target && target !== cardContainer && target.tagName !== 'BUTTON') {
            target = target.parentNode;
        }
        if (!target || target.tagName !== 'BUTTON' || target.disabled) return;
        var id = target.getAttribute('data-id');
        if (target.classList.contains('btn-add')) {
            sendFriendRequest(id);
        } else if (target.classList.contains('btn-accept')) {
            acceptRequest(id);
        } else if (target.classList.contains('btn-refuse')) {
            declineRequest(id);
        } else if (target.classList.contains('btn-view')) {
            window.location.href = 'view_profile.php?user_id=' + id;
        } else if (target.classList.contains('btn-msg')) {
            window.location.href = 'mesaje.php?id=' + id;
        } else if (target.classList.contains('btn-del')) {
            removeFriend(id);
        } else if (target.classList.contains('btn-block')) {
            alert('Feature unavailable');
        }
    });

    function sendFriendRequest(id) {
        fetch('friend_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'send_request', user_id: id })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.success) {
                online = online.map(function (u) {
                    if (u.id == id) {
                        u.requestSent = true;
                    }
                    return u;
                });
                if (currentTab === 'online') {
                    currentList = online.slice();
                    render();
                }
            } else {
                alert(d.message || 'Error');
            }
        });
    }

    function acceptRequest(id) {
        fetch('friend_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'accept_request', user_id: id })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.success) {
                requests = requests.filter(function (u) { return u.id != id; });
                d.user.isFriend = true;
                friends.push(d.user);
                if (d.user.status !== 'offline') {
                    online.push(d.user);
                }
                if (currentTab === 'requests') {
                    currentList = requests.slice();
                } else if (currentTab === 'friends') {
                    currentList = friends.slice();
                } else if (currentTab === 'online') {
                    currentList = online.slice();
                }
                render();
            } else {
                alert(d.message || 'Error');
            }
        });
    }

    function declineRequest(id) {
        fetch('friend_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'decline_request', user_id: id })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.success) {
                requests = requests.filter(function (u) { return u.id != id; });
                if (d.user) {
                    online.push(d.user);
                }
                if (currentTab === 'requests') {
                    currentList = requests.slice();
                    render();
                }
            } else {
                alert(d.message || 'Error');
            }
        });
    }

    function removeFriend(id) {
        fetch('friend_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'remove_friend', user_id: id })
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (d.success) {
                friends = friends.filter(function (u) { return u.id != id; });
                online = online.map(function (u) {
                    if (u.id == id) {
                        delete u.isFriend;
                    }
                    return u;
                });
                if (currentTab === 'friends') {
                    currentList = friends.slice();
                } else if (currentTab === 'online') {
                    currentList = online.slice();
                }
                render();
            } else {
                alert(d.message || 'Error');
            }
        });
    }

    render();
})();