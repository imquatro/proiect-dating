document.addEventListener('DOMContentLoaded', () => {
    const navDot = document.getElementById('msgAlert');
    function refreshUnread(){
        fetch('check_unread.php')
            .then(r => r.json())
            .then(data => {
                if(data.total && data.total > 0){
                    if(navDot) navDot.classList.add('show');
                } else {
                    if(navDot) navDot.classList.remove('show');
                }
                if(typeof window.updateContactUnread === 'function'){
                    window.updateContactUnread(data.details || []);
                }
            })
            .catch(() => {});
    }
    refreshUnread();
    setInterval(refreshUnread, 5000);
});