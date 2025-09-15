document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a.nav-btn').forEach(link => {
    link.addEventListener('click', e => {
      const href = link.getAttribute('href');
      if (!href || href === '#' || link.classList.contains('active')) {
        return;
      }
      e.preventDefault();
      startPageTransition(link.href);
    });
  });
});

function startPageTransition(url) {
  const overlay = document.createElement('div');
  overlay.id = 'page-transition';
  overlay.innerHTML = `
    <div class="line"></div>
    <div class="light left"></div>
    <div class="light right"></div>
    <div class="percent">0%</div>
  `;
  document.body.appendChild(overlay);

  const app = document.querySelector('.app-frame');
  const topClone = app.cloneNode(true);
  const bottomClone = app.cloneNode(true);
  topClone.classList.add('app-split', 'top');
  bottomClone.classList.add('app-split', 'bottom');
  document.body.appendChild(topClone);
  document.body.appendChild(bottomClone);
  app.style.visibility = 'hidden';

  const percentEl = overlay.querySelector('.percent');
  const minDuration = 1500;
  const start = performance.now();
  let loaded = 0;
  let total = 0;

  fetch(url, { credentials: 'include' }).then(response => {
    total = parseInt(response.headers.get('Content-Length')) || 0;
    const reader = response.body && response.body.getReader();
    if (!reader) {
      finalize();
      return;
    }
    function read() {
      reader.read().then(({ done, value }) => {
        if (done) {
          finalize();
          return;
        }
        loaded += value.byteLength;
        if (total) {
          const pct = Math.min(Math.round((loaded / total) * 100), 99);
          percentEl.textContent = pct + '%';
        } else {
          const elapsed = performance.now() - start;
          const pct = Math.min(Math.round((elapsed / minDuration) * 90), 90);
          percentEl.textContent = pct + '%';
        }
        read();
      }).catch(finalize);
    }
    read();
  }).catch(finalize);

  function finalize() {
    const elapsed = performance.now() - start;
    const wait = Math.max(minDuration - elapsed, 0);
    setTimeout(() => {
      percentEl.textContent = '100%';
      topClone.classList.add('animate');
      bottomClone.classList.add('animate');
      setTimeout(() => {
        window.location.href = url;
      }, 700);
    }, wait);
  }
}