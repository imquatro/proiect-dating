document.addEventListener('DOMContentLoaded', function() {
    const avatar = document.querySelector('.mini-profile-avatar');
    if (avatar) {
        avatar.addEventListener('click', function() {
            console.log('Mini profile avatar clicked');
        });
    }
});