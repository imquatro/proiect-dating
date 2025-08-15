document.addEventListener('DOMContentLoaded', () => {
    const slotsEl = document.getElementById('barn-slots');
    const capacity = 4;

    async function loadBarn() {
        const res = await fetch('barn_items.php');
        const items = await res.json();
        slotsEl.innerHTML = '';
        const displayed = items.slice(0, capacity);
        displayed.forEach(it => {
            const slot = document.createElement('div');
            slot.className = 'barn-slot';
            slot.innerHTML = `<img src="${it.image}" alt=""><div class="quantity">${it.quantity}</div>`;
            slotsEl.appendChild(slot);
        });
        for (let i = displayed.length; i < capacity; i++) {
            const slot = document.createElement('div');
            slot.className = 'barn-slot empty';
            slotsEl.appendChild(slot);
        }
    }

    loadBarn();
    document.addEventListener('barnUpdated', loadBarn);
});