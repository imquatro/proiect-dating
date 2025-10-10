(function () {
    var cardContainer = document.getElementById('cardContainer');
    var searchInput = document.getElementById('searchInput');
    var searchBtn = document.getElementById('searchBtn');
    var tabButtons = document.querySelectorAll('.tab-btn');
    var contentEl = document.querySelector('.content');

    var currentTab = localStorage.getItem('friendsActiveTab') || 'online';
    var visibleCount = 10;
    var users = Array.isArray(window.allUsers) ? window.allUsers : [];
    users.sort(function (a, b) {
        var order = { online: 0, idle: 1, offline: 2 };
        var sa = order[a.status] !== undefined ? order[a.status] : 3;
        var sb = order[b.status] !== undefined ? order[b.status] : 3;
        if (sa === sb) {
            return a.username.localeCompare(b.username);
        }
        return sa - sb;
    });
    var requests = Array.isArray(window.friendRequests) ? window.friendRequests : [];
    var friends = Array.isArray(window.friends) ? window.friends : [];
    var currentList = users.slice();

    function statusClass(status) {
        if (status === 'online') return 'status-online';
        if (status === 'idle') return 'status-idle';
        return 'status-offline';
    }

    function formatLastSeen(ts) {
        var diff = Math.floor(Date.now() / 1000) - ts;
        if (diff < 60) return 'Last seen just now';
        var mins = Math.floor(diff / 60);
        if (mins < 60) return 'Last seen ' + mins + ' min ago';
        var hrs = Math.floor(mins / 60);
        if (hrs < 24) return 'Last seen ' + hrs + ' h ago';
        var days = Math.floor(hrs / 24);
        return 'Last seen ' + days + ' d ago';
    }

    function buttonsHtml(tab, id, requested, isFriend) {
        if (tab === 'online') {
            var html = '<a class="btn-farm" href="vizitfarm/vizitfarm.php?id=' + encodeURIComponent(id) + '" title="Visit farm"><i class="fas fa-seedling"></i></a>';
            if (isFriend) {
                html += '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                    '<button class="btn-msg" data-id="' + id + '" title="Message"><i class="fas fa-envelope"></i></button>' +
                    '<button class="btn-del" data-id="' + id + '" title="Delete"><i class="fas fa-trash"></i></button>' +
                    '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
            } else {
                if (!requested) {
                    html += '<button class="btn-add" data-id="' + id + '" title="Add friend"><i class="fas fa-user-plus"></i></button>';
                }
                html += '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                    '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
            }
            return html;
        }
        if (tab === 'requests') {
            return '<a class="btn-farm" href="vizitfarm/vizitfarm.php?id=' + encodeURIComponent(id) + '" title="Visit farm"><i class="fas fa-seedling"></i></a>' +
                '<button class="btn-view" data-id="' + id + '" title="View profile"><i class="fas fa-eye"></i></button>' +
                '<button class="btn-accept" data-id="' + id + '" title="Accept"><i class="fas fa-check"></i></button>' +
                '<button class="btn-refuse" data-id="' + id + '" title="Decline"><i class="fas fa-times"></i></button>' +
                '<button class="btn-block" data-id="' + id + '" title="Block"><i class="fas fa-ban"></i></button>';
        }
        if (tab === 'friends') {
            return '<a class="btn-farm" href="vizitfarm/vizitfarm.php?id=' + encodeURIComponent(id) + '" title="Visit farm"><i class="fas fa-seedling"></i></a>' +
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
                '<div class="user-card-text">' +
                    '<div class="user-card-name' + (u.vip ? ' gold-shimmer' : '') + '">' + u.username + '</div>' +
                    '<div class="user-card-last-seen">' + formatLastSeen(u.last_active) + '</div>' +
                '</div>' +
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
            currentList = users.slice();
            searchInput.disabled = false;
        } else if (tab === 'requests') {
            currentList = requests.slice();
            searchInput.disabled = true;
        } else {
            currentList = friends.slice();
            searchInput.disabled = true;
        }
        localStorage.setItem('friendsActiveTab', tab);
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
            currentList = users.slice();
        } else {
            currentList = users.filter(function (u) {
                return u.username.toLowerCase().indexOf(val) !== -1;
            });
        }
        currentList.sort(function (a, b) {
            return a.username.localeCompare(b.username);
        });
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
                currentList = users.slice();
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
                users = users.map(function (u) {
                    if (u.id == id) {
                        u.requestSent = true;
                    }
                    return u;
                });
                if (currentTab === 'online') {
                    currentList = users.slice();
                    render();
                }
            } else {
                if (d.message === 'friend_limit_reached') {
                    showFriendLimitPopup(d.friend_count);
                } else {
                    alert(d.message || 'Error');
                }
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
                users.push(d.user);
                if (currentTab === 'requests') {
                    currentList = requests.slice();
                } else if (currentTab === 'friends') {
                    currentList = friends.slice();
                } else if (currentTab === 'online') {
                    currentList = users.slice();
                }
                render();
                if (window.updateFriendRequestIndicators) {
                    window.updateFriendRequestIndicators(requests.length);
                }
            } else {
                if (d.message === 'friend_limit_reached') {
                    showFriendLimitPopup(d.friend_count);
                } else {
                    alert(d.message || 'Error');
                }
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
                    users.push(d.user);
                }
                if (currentTab === 'requests') {
                    currentList = requests.slice();
                    render();
                }
                if (window.updateFriendRequestIndicators) {
                    window.updateFriendRequestIndicators(requests.length);
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
                users = users.map(function (u) {
                    if (u.id == id) {
                        delete u.isFriend;
                    }
                    return u;
                });
                if (currentTab === 'friends') {
                    currentList = friends.slice();
                } else if (currentTab === 'online') {
                    currentList = users.slice();
                }
                render();
            } else {
                alert(d.message || 'Error');
            }
        });
    }

    // Funcție pentru afișarea pop-up-ului de limită de prieteni
    function showFriendLimitPopup(friendCount) {
        const popup = document.createElement('div');
        popup.className = 'friend-limit-popup';
        popup.innerHTML = `
            <div class="popup-content">
                <div class="popup-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Limita de Prieteni Atinsă</h3>
                <p>Ai deja <strong>${friendCount}/20</strong> prieteni.</p>
                <p>Pentru a adăuga prieteni noi, trebuie să elimini unii dintre prietenii actuali.</p>
                <button onclick="this.parentElement.parentElement.remove()" class="popup-btn">
                    <i class="fas fa-check"></i> Înțeleg
                </button>
            </div>
        `;
        
        // Adaugă stilurile
        const style = document.createElement('style');
        style.textContent = `
            .friend-limit-popup {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease;
            }
            
            .popup-content {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 30px;
                border-radius: 15px;
                text-align: center;
                color: white;
                max-width: 400px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                animation: slideIn 0.3s ease;
            }
            
            .popup-icon {
                font-size: 48px;
                margin-bottom: 20px;
                color: #ffd700;
            }
            
            .popup-content h3 {
                margin: 0 0 15px 0;
                font-size: 24px;
                font-weight: bold;
            }
            
            .popup-content p {
                margin: 10px 0;
                font-size: 16px;
                line-height: 1.5;
            }
            
            .popup-btn {
                background: linear-gradient(45deg, #ff6b6b, #ee5a24);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 25px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                margin-top: 20px;
                transition: transform 0.2s ease;
            }
            
            .popup-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes slideIn {
                from { transform: translateY(-50px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(popup);
        
        // Elimină stilurile când se închide popup-ul
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                popup.remove();
                style.remove();
            }
        });
    }

    setTab(currentTab);
    if (window.updateFriendRequestIndicators) {
        window.updateFriendRequestIndicators(requests.length);
    }
})();
