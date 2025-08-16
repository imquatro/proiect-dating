document.addEventListener('DOMContentLoaded', () => {
    const slotsEl = document.getElementById('barn-slots');

    async function loadBarn() {
        const res = await fetch('barn_items.php');
        const data = await res.json();
        const capacity = data.capacity || 16;
        const items = data.items || [];
        const map = {};
        for (const it of items) {
            map[it.slot] = it;
        }
        slotsEl.innerHTML = '';
        for (let i = 1; i <= capacity; i++) {
            const slot = document.createElement('div');
            const it = map[i];
            if (it) {
                slot.className = 'barn-slot';
                slot.dataset.item = it.item_id;
                slot.innerHTML = `<img src="${it.image}" alt=""><div class="quantity">${it.quantity}</div>`;
            } else {
                slot.className = 'barn-slot empty';
            }
            slotsEl.appendChild(slot);
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
            empty.innerHTML = `<img src="${item.image}" alt=""><div class="quantity">${add}</div>`;
            remaining -= add;
        }
    }

    loadBarn();
    document.addEventListener('barnUpdated', loadBarn);
    document.addEventListener('barnAddItem', e => addItem(e.detail));
});