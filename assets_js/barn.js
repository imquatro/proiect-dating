document.addEventListener('DOMContentLoaded', () => {
    const slotsEl = document.getElementById('barn-slots');

    function openSell(slot) {
        const itemId = parseInt(slot.dataset.item, 10);
        const slotNumber = parseInt(slot.dataset.slot, 10);
        const name = slot.dataset.name || '';
        const price = parseInt(slot.dataset.price, 10) || 0;
        const qtyEl = slot.querySelector('.quantity');
        const maxQty = qtyEl ? parseInt(qtyEl.textContent, 10) : 1;
        const imgSrc = slot.querySelector('img').src;

        const overlay = document.createElement('div');
        overlay.className = 'sell-overlay';
        overlay.innerHTML = `
            <div class="sell-card">
                <img src="${imgSrc}" alt="">
                <div class="sell-name">${name}</div>
                <div class="sell-qty">
                    <button class="dec">-</button>
                    <input type="number" min="1" max="${maxQty}" value="${maxQty}">
                    <button class="inc">+</button>
                </div>
                <div class="sell-total">${price * maxQty}</div>
                <button class="sell-btn">Sell</button>
            </div>`;
        document.body.appendChild(overlay);

        const input = overlay.querySelector('input');
        const totalEl = overlay.querySelector('.sell-total');

        function updateTotal() {
            let val = parseInt(input.value, 10);
            if (isNaN(val) || val < 1) val = 1;
            if (val > maxQty) val = maxQty;
            input.value = val;
            totalEl.textContent = price * val;
        }

        overlay.querySelector('.dec').addEventListener('click', () => {
            input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
            updateTotal();
        });
        overlay.querySelector('.inc').addEventListener('click', () => {
            input.value = Math.min(maxQty, (parseInt(input.value, 10) || 1) + 1);
            updateTotal();
        });
        input.addEventListener('input', updateTotal);

        overlay.addEventListener('click', e => {
            if (e.target === overlay) overlay.remove();
        });

        overlay.querySelector('.sell-btn').addEventListener('click', async () => {
            const qty = parseInt(input.value, 10) || 1;
            try {
                const res = await fetch('barn_sell.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `item_id=${itemId}&slot=${slotNumber}&quantity=${qty}`
                });
                const data = await res.json();
                if (data.success) {
                    overlay.remove();
                    document.dispatchEvent(new Event('barnUpdated'));
                    if (typeof updateCurrency === 'function') {
                        updateCurrency(data.money, data.gold);
                    }
                }
            } catch (err) {
                console.error('Sell failed', err);
            }
        });
    }

    function renderSlots(capacity, items) {
        const map = {};
        for (const it of items) {
            map[it.slot] = it;
        }
        slotsEl.innerHTML = '';
        for (let i = 1; i <= capacity; i++) {
            const slot = document.createElement('div');
            const it = map[i];
            slot.dataset.slot = i;
            if (it) {
                slot.className = 'barn-slot';
                slot.dataset.item = it.item_id;
                slot.dataset.name = it.name;
                slot.dataset.price = it.sell_price;
                slot.innerHTML = `<img src="${it.image}" alt=""><div class="quantity">${it.quantity}</div>`;
                slot.addEventListener('click', () => openSell(slot));
            } else {
                slot.className = 'barn-slot empty';
            }
            slotsEl.appendChild(slot);
        }
    }

    async function loadBarn() {
        try {
            const res = await fetch('barn_items.php');
            if (!res.ok) throw new Error('network');
            const data = await res.json();
            renderSlots(data.capacity || 16, data.items || []);
        } catch (err) {
            console.error('Failed to load barn data', err);
            renderSlots(16, []);
        }
    }

    function addItem(item) {
        const slots = Array.from(slotsEl.querySelectorAll('.barn-slot'));
        const maxPerSlot = item.quantity === 1 ? 1 : 1000;
        let remaining = item.quantity;

        if (maxPerSlot > 1) {
            for (const slot of slots) {
                if (parseInt(slot.dataset.item, 10) === item.item_id) {
                    const qtyEl = slot.querySelector('.quantity');
                    const current = qtyEl ? parseInt(qtyEl.textContent, 10) : 0;
                    const space = maxPerSlot - current;
                    if (space > 0) {
                        const add = Math.min(space, remaining);
                        qtyEl.textContent = current + add;
                        remaining -= add;
                        if (remaining <= 0) return;
                    }
                }
            }
        }

        while (remaining > 0) {
            const empty = slotsEl.querySelector('.barn-slot.empty');
            if (!empty) break;
            const add = Math.min(maxPerSlot, remaining);
            empty.classList.remove('empty');
            empty.dataset.item = item.item_id;
            empty.dataset.name = item.name || '';
            empty.dataset.price = item.sell_price || 0;
            empty.innerHTML = `<img src="${item.image}" alt=""><div class="quantity">${add}</div>`;
            remaining -= add;
        }
    }

    loadBarn();
    document.addEventListener('barnUpdated', loadBarn);
    document.addEventListener('barnAddItem', e => addItem(e.detail));
});
