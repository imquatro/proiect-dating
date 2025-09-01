document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('achievementSelect');
    const btn = document.getElementById('deleteAchievement');
    if (select && btn) {
        select.addEventListener('change', () => {
            btn.disabled = !select.value;
        });
    }
});