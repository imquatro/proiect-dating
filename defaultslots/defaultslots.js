document.addEventListener('DOMContentLoaded', () => {
    const slots = document.querySelectorAll('.ds-slot.open');
    slots.forEach(slot => {
        slot.addEventListener('click', () => {
            document.querySelectorAll('.ds-slot').forEach(s => s.classList.remove('active'));
            slot.classList.add('active');
        });
    });
});