document.addEventListener('DOMContentLoaded', function() {
    const avatar = document.querySelector('.mini-profile-avatar');
    if (avatar) {
        avatar.addEventListener('click', function() {
            console.log('Mini profile avatar clicked');
        });
    }

    const helpersCard = document.getElementById('helpersCard');
    if (helpersCard) {
        fetch('recent_helpers.php')
            .then(response => response.json())
            .then(data => {
                helpersCard.innerHTML = '';
                data.forEach(helper => {
                    const item = document.createElement('div');
                    item.className = 'helper-item';
                    item.innerHTML = `
                        <img src="${helper.photo}" alt="${helper.username}">
                        <div class="helper-counts">🍖 ${helper.feed} | 💧 ${helper.water}</div>`;
                    helpersCard.appendChild(item);
                });
            })
            .catch(() => {
                helpersCard.innerHTML = '';
            });
    }
});