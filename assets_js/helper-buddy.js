(function(){
    const buddy = {
        messages: {},
        image: null,
        container: null,
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
                        const queue = [];
                        if (!sessionStorage.getItem('welcomeShown')) {
                            queue.push('welcome');
                            sessionStorage.setItem('welcomeShown', '1');
                        }
                        const prev = JSON.parse(sessionStorage.getItem('lastNeeds') || '{}');
                        const current = {
                            water: info.needWater || 0,
                            feed: info.needFeed || 0,
                            harvest: info.needHarvest || 0
                        };
                        if ((prev.water || 0) === 0 && current.water > 0) {
                            queue.push('need_water');
                        } else if ((prev.water || 0) > 0 && current.water === 0) {
                            queue.push('all_watered');
                        }
                        if ((prev.feed || 0) === 0 && current.feed > 0) {
                            queue.push('need_feed');
                        } else if ((prev.feed || 0) > 0 && current.feed === 0) {
                            queue.push('all_fed');
                        }
                        if ((prev.harvest || 0) === 0 && current.harvest > 0) {
                            queue.push('need_harvest');
                        } else if ((prev.harvest || 0) > 0 && current.harvest === 0) {
                            queue.push('all_harvested');
                        }
                        sessionStorage.setItem('lastNeeds', JSON.stringify(current));
                        this.playQueue(queue);
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
        playQueue(queue){
            if (!queue.length) return;
            this.showMessage(queue.shift());
            if (queue.length) {
                setTimeout(() => this.playQueue(queue), 4000);
            }
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
    window.addEventListener('offline', () => {
        sessionStorage.removeItem('welcomeShown');
        sessionStorage.removeItem('lastNeeds');
    });
    window.addEventListener('online', () => {
        buddy.load();
    });
})();