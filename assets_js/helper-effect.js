document.addEventListener('DOMContentLoaded', () => {
    const effect = document.getElementById('helper-effect');
    if (!effect) return;
    const img = effect.querySelector('img');
    let comboEl = effect.querySelector('.combo-count');
    if (!comboEl) {
        comboEl = document.createElement('div');
        comboEl.className = 'combo-count';
        effect.appendChild(comboEl);
    }

    const totalSlots = document.querySelectorAll('.farm-slot').length || 1;
    const DURATION = 500; // ms
    const COMBO_TIMEOUT = 5000; // 5 seconds
    const pageLoadTime = Date.now();
    let currentHelper = null;
    let lastTimestamp = 0;
    let lastClicks = 0;
    let combo = 0;
    let fadeTimer = null;
    let hideTimer = null;
    const queue = [];
    const combos = {};

    function getColor(ratio) {
        // ratio 0..1 -> white -> orange -> red
        if (ratio < 0.5) {
            const t = ratio / 0.5;
            const g = Math.round(255 - (255 - 165) * t);
            const b = Math.round(255 - 255 * t);
            return `rgb(255,${g},${b})`;
        }
        const t = (ratio - 0.5) / 0.5;
        const g = Math.round(165 - 165 * t);
        return `rgb(255,${g},0)`;
    }

    function updateCombo() {
        if (combo > 0) {
            comboEl.textContent = combo;
            comboEl.style.display = 'flex';
            comboEl.style.color = getColor(Math.min(combo / totalSlots, 1));
            comboEl.classList.remove('pulse');
            void comboEl.offsetWidth;
            comboEl.classList.add('pulse');
        } else {
            comboEl.style.display = 'none';
        }
    }


    function scheduleFade() {
        if (fadeTimer) clearTimeout(fadeTimer);
        if (hideTimer) clearTimeout(hideTimer);
        fadeTimer = setTimeout(() => {
            effect.style.opacity = '0';
            hideTimer = setTimeout(() => {
                effect.style.display = 'none';
                currentHelper = null;
                combo = 0;
                updateCombo();
                if (queue.length) {
                    const next = queue.shift();
                    showHelper(next);
                }
            }, DURATION);
        }, DURATION);
    }

    function showHelper(data) {
        currentHelper = data.helper_id;
        combo = data.clicks || 1;
        img.src = data.photo;
        effect.style.display = 'flex';
        effect.style.opacity = '0';
        requestAnimationFrame(() => {
            effect.style.opacity = '1';
        });
        updateCombo();
        scheduleFade();
    }

    function handleEvent(data) {
        const helperId = data.helper_id;
        if (currentHelper === helperId) {
            combo = data.clicks || combo + 1;
            effect.style.display = 'flex';
            effect.style.opacity = '1';
            updateCombo();
            scheduleFade();
            return;
        }
        if (currentHelper !== null) {
            queue.push(data);
            return;
        }
        showHelper(data);
    }

    function poll() {
        fetch((window.baseUrl || '') + 'last_helper.php', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (!data.helper_id) return;
                const ts = new Date(data.helped_at).getTime();
                const clicks = data.clicks || 1;
                if (ts <= pageLoadTime || (ts === lastTimestamp && clicks === lastClicks)) return;
                lastTimestamp = ts;
                lastClicks = clicks;
                handleEvent(data);
            })
            .catch(() => {});
    }

    setInterval(poll, 500);
});