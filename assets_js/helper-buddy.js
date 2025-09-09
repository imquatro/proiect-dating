(function(){
    const buddy = {
        messages: {},
        image: null,
        container: null,
        shown: { water: false, feed: false, harvest: false },
        ready: false,
        hideTimer: null,
        load(){
            const base = window.baseUrl || '';
            fetch(base + 'helper_messages.json')
                .then(r => r.json())
                .then(data => { this.messages = data; })
                .catch(()=>{});
            fetch(base + 'helper_info.php', { credentials: 'same-origin' })
                .then(r => r.json())
                .then(info => {
                    if (info.helper && info.helper.image) {
                        this.image = info.helper.image;
                        this.create();
                        this.ready = true;
                        this.showMessage('welcome');
                    }
                })
                .catch(()=>{});
        },
        create(){
            if (this.container || !this.image) return;
            const div = document.createElement('div');
            div.id = 'helper-buddy';
            div.innerHTML = `<div class="helper-text"></div><img src="${this.image}" alt="helper">`;
            const parent = document.querySelector('.content') || document.body;
            parent.appendChild(div);
            this.container = div;
        },
        showMessage(type){
            if (!this.ready || !this.messages[type]) return;
            const arr = this.messages[type];
            const msg = arr[Math.floor(Math.random() * arr.length)];
            const textEl = this.container.querySelector('.helper-text');
            textEl.textContent = msg;
            this.container.style.display = 'flex';
            clearTimeout(this.hideTimer);
            this.hideTimer = setTimeout(() => {
                this.container.style.display = 'none';
            }, 4000);
        }
    };
    window.helperBuddy = buddy;
    document.addEventListener('DOMContentLoaded', () => buddy.load());
})();