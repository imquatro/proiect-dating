(function(){
    const cardContainer = document.getElementById('cardContainer');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const tabButtons = document.querySelectorAll('.tab-btn');

    let currentTab = 'online';
    let visibleCount = 10;
    let currentList = onlineUsers.slice();

    function statusClass(status){
        if(status === 'online') return 'status-online';
        if(status === 'idle') return 'status-idle';
        return 'status-offline';
    }

    function buttonsHtml(tab, id, requested){
        if(tab === 'online'){
            let html = '<button class="btn-view" data-id="'+id+'" title="Vezi profil"><i class="fas fa-eye"></i></button>';
            if(!requested){
                html += '<button class="btn-add" data-id="'+id+'" title="Adauga prieten"><i class="fas fa-user-plus"></i></button>';
            }
            html += '<button class="btn-block" data-id="'+id+'" title="Blocheaza"><i class="fas fa-ban"></i></button>';
            return html;
        }
        if(tab === 'requests'){
            return '<button class="btn-view" data-id="'+id+'" title="Vezi profil"><i class="fas fa-eye"></i></button>'+
                   '<button class="btn-accept" data-id="'+id+'" title="Accepta"><i class="fas fa-check"></i></button>'+
                   '<button class="btn-refuse" data-id="'+id+'" title="Refuza"><i class="fas fa-times"></i></button>'+
                   '<button class="btn-block" data-id="'+id+'" title="Blocheaza"><i class="fas fa-ban"></i></button>';
        }
        if(tab === 'friends'){
            return '<button class="btn-view" data-id="'+id+'" title="Vezi profil"><i class="fas fa-eye"></i></button>'+
                   '<button class="btn-msg" data-id="'+id+'" title="Mesaj"><i class="fas fa-envelope"></i></button>'+
                   '<button class="btn-del" data-id="'+id+'" title="Sterge"><i class="fas fa-trash"></i></button>'+
                   '<button class="btn-block" data-id="'+id+'" title="Blocheaza"><i class="fas fa-ban"></i></button>';
        }
        return '';
    }

    function render(){
        cardContainer.innerHTML='';
        const list = currentList.slice(0, visibleCount);
        list.forEach(u => {
            const div = document.createElement('div');
            div.className = 'user-card';
            div.innerHTML = '<span class="status-dot '+statusClass(u.status)+'"></span>'+
                '<img src="'+u.avatar+'" class="user-card-avatar" alt="">'+
                '<div class="user-card-name">'+u.username+'</div>'+
                '<div class="user-card-buttons">'+buttonsHtml(currentTab, u.id, u.requestSent)+'</div>';
            cardContainer.appendChild(div);
        });
    }

    function setTab(tab){
        currentTab = tab;
        tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
        searchInput.value = '';
        visibleCount = 10;
        if(tab === 'online'){
            currentList = onlineUsers.slice();
            searchInput.disabled = false;
        }else if(tab === 'requests'){
            currentList = friendRequests.slice();
            searchInput.disabled = true;
        }else{
            currentList = friends.slice();
            searchInput.disabled = true;
        }
        render();
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => setTab(btn.dataset.tab));
    });

    searchInput.addEventListener('input', () => {
        if(currentTab !== 'online') return;
        const val = searchInput.value.toLowerCase();
        if(!val){
            currentList = onlineUsers.slice();
        }else{
            currentList = onlineUsers.filter(u => u.username.toLowerCase().includes(val));
        }
        visibleCount = currentList.length;
        render();
    });

    searchBtn.addEventListener('click', () => {
        searchInput.dispatchEvent(new Event('input'));
    });

    document.addEventListener('click', (e) => {
        if(!searchInput.contains(e.target) && !cardContainer.contains(e.target)){
            if(searchInput.value){
                searchInput.value = '';
                currentList = onlineUsers.slice();
                visibleCount = 10;
                render();
            }
        }
    });

    cardContainer.addEventListener('scroll', () => {
        if(cardContainer.scrollTop + cardContainer.clientHeight >= cardContainer.scrollHeight - 5){
            if(visibleCount < currentList.length){
                visibleCount += 5;
                render();
            }
        }
    });

    cardContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if(!btn) return;
        const id = btn.dataset.id;
        if(btn.classList.contains('btn-add')){
            sendFriendRequest(id);
        } else if(btn.classList.contains('btn-accept')){
            acceptRequest(id);
        } else if(btn.classList.contains('btn-refuse')){
            declineRequest(id);
        } else if(btn.classList.contains('btn-view')){
            window.location.href = 'view_profile.php?user_id=' + id;
        } else if(btn.classList.contains('btn-msg')){
            window.location.href = 'messages.php?user_id=' + id;
        } else if(btn.classList.contains('btn-del') || btn.classList.contains('btn-block')){
            alert('Funcție indisponibilă');
        }
    });

    function sendFriendRequest(id){
        fetch('friend_actions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'send_request', user_id:id})
        }).then(r=>r.json()).then(d=>{
            if(d.success){
                onlineUsers = onlineUsers.map(u => {
                    if(u.id == id){
                        u.requestSent = true;
                    }
                    return u;
                });
                if(currentTab === 'online'){
                    currentList = onlineUsers.slice();
                    render();
                }
            } else {
                alert(d.message || 'Eroare');
            }
        });
    }

    function acceptRequest(id){
        fetch('friend_actions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'accept_request', user_id:id})
        }).then(r=>r.json()).then(d=>{
            if(d.success){
                friendRequests = friendRequests.filter(u => u.id != id);
                friends.push(d.user);
                if(currentTab === 'requests'){
                    currentList = friendRequests.slice();
                } else if(currentTab === 'friends'){
                    currentList = friends.slice();
                }
                render();
            } else {
                alert(d.message || 'Eroare');
            }
        });
    }

    function declineRequest(id){
        fetch('friend_actions.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action:'decline_request', user_id:id})
        }).then(r=>r.json()).then(d=>{
            if(d.success){
                friendRequests = friendRequests.filter(u => u.id != id);
                if(d.user){
                    onlineUsers.push(d.user);
                }
                if(currentTab === 'requests'){
                    currentList = friendRequests.slice();
                    render();
                }
            } else {
                alert(d.message || 'Eroare');
            }
        });
    }

    render();
})();
