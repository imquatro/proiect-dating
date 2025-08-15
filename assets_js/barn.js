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
                slot.innerHTML = `<img src="${it.image}" alt=""><div class="quantity">${it.quantity}</div>`;
            } else {
                slot.className = 'barn-slot empty';
            }
            slotsEl.appendChild(slot);
        }
    }

    loadBarn();
    document.addEventListener('barnUpdated', loadBarn);
});