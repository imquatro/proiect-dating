function initAchievementDelete(root = document) {
    const lookup = root.querySelector ? root.querySelector.bind(root) : () => null;
    const select = lookup('#achievementSelect');
    const btn = lookup('#deleteAchievement');
    if (select && btn) {
        select.addEventListener('change', () => {
            btn.disabled = !select.value;
        });
    }
}
document.addEventListener('DOMContentLoaded', () => initAchievementDelete());