document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.getElementById('progress-bar');
    const messageElem = document.getElementById('message');
    const images = JSON.parse(document.getElementById('image-data').textContent);
    let loaded = 0;

    function updateProgress() {
        loaded++;
        const percent = Math.round((loaded / images.length) * 100);
        progressBar.style.width = percent + '%';
        if (loaded === images.length) {
            setTimeout(() => {
                window.location.href = 'welcome.php';
            }, 500);
        }
    }

    images.forEach(src => {
        const img = new Image();
        img.onload = updateProgress;
        img.onerror = updateProgress;
        img.src = src;
    });

    fetch('loading_messages.txt')
        .then(r => r.text())
        .then(text => {
            const lines = text.split('\n').filter(Boolean);
            if (lines.length) {
                function showRandom() {
                    const idx = Math.floor(Math.random() * lines.length);
                    messageElem.textContent = lines[idx];
                }
                showRandom();
                setInterval(showRandom, 3000);
            }
        });
});