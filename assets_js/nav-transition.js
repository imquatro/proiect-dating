document.addEventListener('DOMContentLoaded', () => {
  const content = document.querySelector('.content');
  if (sessionStorage.getItem('navFading')) {
    sessionStorage.removeItem('navFading');
    content?.classList.add('nav-fade-show');
    content?.addEventListener('animationend', () => {
      document.documentElement.classList.remove('nav-fade');
      content.classList.remove('nav-fade-show');
    }, { once: true });
  } else {
    document.documentElement.classList.remove('nav-fade');
    content?.classList.remove('nav-fade-show');
  }
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
  const app = document.querySelector('.app-frame');
  const content = app.querySelector('.content');
  const rect = content.getBoundingClientRect();

  const overlay = document.createElement('div');
  overlay.id = 'page-transition';
  overlay.innerHTML = `
    <div class="line"></div>
    <div class="bolt left"></div>
    <div class="bolt right"></div>
    <div class="flash"></div>
    <div class="percent">0%</div>
  `;
  Object.assign(overlay.style, {
    top: rect.top + 'px',
    left: rect.left + 'px',
    width: rect.width + 'px',
    height: rect.height + 'px'
  });
  document.body.appendChild(overlay);

  const topClone = content.cloneNode(true);
  const bottomClone = content.cloneNode(true);
  topClone.classList.add('app-split', 'top');
  bottomClone.classList.add('app-split', 'bottom');
  [topClone, bottomClone].forEach(clone => {
    clone.style.background = 'none';
    Object.assign(clone.style, {
      top: rect.top + 'px',
      left: rect.left + 'px',
      width: rect.width + 'px',
      height: rect.height + 'px'
    });
    document.body.appendChild(clone);
  });
  content.style.visibility = 'hidden';

  const percentEl = overlay.querySelector('.percent');
  const minDuration = 150;
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
      }).catch(() => finalize());
    }
    read();
  }).catch(() => finalize());

  function finalize() {
    const elapsed = performance.now() - start;
    const wait = Math.max(minDuration - elapsed, 0);
    setTimeout(() => {
      percentEl.textContent = '100%';
      overlay.classList.add('flash');
      const flashEl = overlay.querySelector('.flash');
      flashEl.addEventListener('animationend', () => {
        topClone.classList.add('animate');
        bottomClone.classList.add('animate');
        setTimeout(() => {
          sessionStorage.setItem('navFading', '1');
          window.location.href = url;
        }, 450);
      }, { once: true });
    }, wait);
  }
}