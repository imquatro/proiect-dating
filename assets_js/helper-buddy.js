(function(){
    // Helper Buddy popup disabled by request. Keep a no-op object to avoid runtime errors.
    const buddy = {
        messages: {},
        image: null,
        container: null,
        ready: false,
        hideTimer: null,
        load(){ /* disabled */ },
        processInfo(info, initial=false){
            const queue = [];
            if (initial && !sessionStorage.getItem('welcomeShown')) {
                queue.push('welcome');
                sessionStorage.setItem('welcomeShown', '1');
            }
            const prev = JSON.parse(sessionStorage.getItem('lastNeeds') || '{}');
            const current = {
                water: info.needWater || 0,
                feed: info.needFeed || 0,
                harvest: info.needHarvest || 0
            };

            if (current.water > 0) {
                queue.push('need_water');
            } else if ((prev.water || 0) > 0 && current.water === 0) {
                queue.push('all_watered');
            }

            if (current.feed > 0) {
                queue.push('need_feed');
            } else if ((prev.feed || 0) > 0 && current.feed === 0) {
                queue.push('all_fed');
            }

            if (current.harvest > 0) {
                queue.push('need_harvest');
            } else if ((prev.harvest || 0) > 0 && current.harvest === 0) {
                queue.push('all_harvested');
            }

            if (current.water === 0 && current.feed === 0 && current.harvest === 0 &&
                ((prev.water || 0) > 0 || (prev.feed || 0) > 0 || (prev.harvest || 0) > 0)) {
                queue.push('all_done');
            }

            sessionStorage.setItem('lastNeeds', JSON.stringify(current));
            this.playQueue(queue);
        },
        checkNeeds(){ /* disabled */ },
        create(){ /* disabled */ },
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
    // Do not auto-initialize; all listeners removed to fully disable popups.
})();