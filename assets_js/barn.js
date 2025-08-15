document.addEventListener('DOMContentLoaded', () => {
    const slotsEl = document.getElementById('barn-slots');
    const capacity = 16;

    async function loadBarn() {
        const res = await fetch('barn_items.php');
        const items = await res.json();
        slotsEl.innerHTML = '';
        for (let i = 0; i < capacity; i++) {
            const slot = document.createElement('div');
            if (items[i]) {
                const it = items[i];
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
        // Try to update an existing slot with the same item
        const slots = slotsEl.querySelectorAll('.barn-slot');
        for (const slot of slots) {
            if (parseInt(slot.dataset.item, 10) === item.item_id) {
                const qty = slot.querySelector('.quantity');
                if (qty) {
                    qty.textContent = parseInt(qty.textContent, 10) + item.quantity;
                }
                return;
            }
        }

        // Otherwise find the first empty slot and fill it
        for (const slot of slots) {
            if (slot.classList.contains('empty')) {
                slot.classList.remove('empty');
                slot.dataset.item = item.item_id;
                slot.innerHTML = `<img src="${item.image}" alt=""><div class="quantity">${item.quantity}</div>`;
                return;
            }
        }
    }

    loadBarn();
    document.addEventListener('barnUpdated', loadBarn);
    document.addEventListener('barnAddItem', e => addItem(e.detail));
});