document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('helpersList');
    if (!list) return;
      fetch('helpers_list.php', { credentials: 'same-origin' })
          .then(res => res.json())
          .then(data => {
              data.helpers.forEach(h => {
                  const card = document.createElement('div');
                  card.className = 'helper-card';
                  card.innerHTML = `<img src="${h.image}" alt="${h.name}"><span>${h.name}</span>`;
                  card.addEventListener('click', () => {
                      fetch('apply_helper.php', {
                          method: 'POST',
                          credentials: 'same-origin',
                          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                          body: 'helper_id=' + encodeURIComponent(h.id)
                      })
                          .then(r => r.json())
                          .then(resp => {
                              if (resp.success) {
                                  list.querySelectorAll('.helper-card').forEach(c => c.classList.remove('selected'));
                                  card.classList.add('selected');
                              }
                          });
                  });
                  list.appendChild(card);
              });
          })
          .catch(() => {});
});