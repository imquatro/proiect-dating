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

  // Get user's preferred loading style
  const loadingStyle = localStorage.getItem('loadingStyle') || 'variant-1';

  const overlay = document.createElement('div');
  overlay.id = 'page-transition';
  overlay.className = `loading-${loadingStyle}`;
  overlay.innerHTML = `
    <div class="nav-overlay">
      <div class="nav-particles"></div>
      <div class="nav-card">
        <div class="nav-icon">
          <div class="nav-icon-inner">
            <i class="fas fa-seedling"></i>
          </div>
        </div>
        <div class="nav-text">
          <span class="nav-main-text">Loading</span>
          <span class="nav-dots">...</span>
        </div>
        <div class="nav-progress-container">
          <div class="nav-progress">
            <div class="nav-progress-bar">
              <div class="nav-progress-glow"></div>
              <span class="nav-percent">0%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
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

  const percentEl = overlay.querySelector('.nav-percent');
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
          const bar = overlay.querySelector('.nav-progress-bar');
          if (bar) {
            bar.style.width = pct + '%';
            bar.style.background = `linear-gradient(90deg, #4caf50, #66bb6a, #4caf50)`;
          }
        } else {
          const elapsed = performance.now() - start;
          const pct = Math.min(Math.round((elapsed / minDuration) * 90), 90);
          percentEl.textContent = pct + '%';
          const bar = overlay.querySelector('.nav-progress-bar');
          if (bar) {
            bar.style.width = pct + '%';
            bar.style.background = `linear-gradient(90deg, #4caf50, #66bb6a, #4caf50)`;
          }
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
      const bar = overlay.querySelector('.nav-progress-bar');
      if (bar) {
        bar.style.width = '100%';
        bar.style.background = 'linear-gradient(90deg, #4caf50, #66bb6a, #4caf50)';
        bar.style.boxShadow = '0 0 20px rgba(76, 175, 80, 0.6)';
      }
      
      // Add completion animation
      const card = overlay.querySelector('.nav-card');
      if (card) {
        card.style.transform = 'scale(1.05)';
        card.style.transition = 'transform 0.3s ease';
      }
      
      topClone.classList.add('animate');
      bottomClone.classList.add('animate');
      setTimeout(() => {
        sessionStorage.setItem('navFading', '1');
        window.location.href = url;
      }, 400);
    }, wait);
  }
}