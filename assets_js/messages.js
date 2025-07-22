// Dummy: simulare selectare conversație
document.querySelectorAll('.message-user').forEach((el, idx) => {
    el.addEventListener('click', function() {
        document.querySelectorAll('.message-user').forEach(e => e.classList.remove('active'));
        this.classList.add('active');
        // În viitor, aici poți face update cu mesaje, avatar, status etc.
        // Acum lasă doar highlight vizual.
    });
});
//scoll la mesajjele limita vizuale din mesagerie
function scrollToLastMessage() {
    var convBody = document.querySelector('.messages-conv-body');
    if (convBody) {
        convBody.scrollTop = convBody.scrollHeight;
    }
}
// Apelezi această funcție DUPĂ ce încarci/adaugi mesaje noi în .messages-conv-body
// De exemplu: după ce inserezi HTML-ul mesajelor
scrollToLastMessage();
//scoll la mesajjele limita vizuale din mesagerie