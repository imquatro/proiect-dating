document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.getElementById('progress-bar');
    const messageElem = document.getElementById('message');
    const images = JSON.parse(document.getElementById('image-data').textContent);
    let loaded = 0;
    let messageInterval;

    function updateProgress() {
        loaded++;
        let percent = Math.round((loaded / images.length) * 100);
        if (loaded === images.length) {
            // Pause at 99% for dramatic effect
            progressBar.style.width = '99%';
            setTimeout(() => {
                progressBar.style.width = '100%';
                setTimeout(() => {
                    window.location.href = 'welcome.php';
                }, 1000);
            }, 5000);
            return;
        }
        progressBar.style.width = percent + '%';
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
                messageInterval = setInterval(showRandom, 3000);
            }
        });
});