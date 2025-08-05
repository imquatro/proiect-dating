document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.farm-slot').forEach(function(slot) {
        slot.addEventListener('click', function() {
            alert('Slot neconfigurat');
        });
    });
});