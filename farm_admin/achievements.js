function initAchievementDelete(root = document) {
    const select = root.getElementById('achievementSelect');
    const btn = root.getElementById('deleteAchievement');
    if (select && btn) {
        select.addEventListener('change', () => {
            btn.disabled = !select.value;
        });
    }
}

document.addEventListener('DOMContentLoaded', () => initAchievementDelete());