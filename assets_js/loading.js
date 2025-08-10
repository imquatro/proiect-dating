document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.getElementById('progress-bar');
    const percentText = document.getElementById('progress-text');
    const messageElem = document.getElementById('message');
    const images = JSON.parse(document.getElementById('image-data').textContent);
    const totalImages = images.length || 1;
    let loaded = 0;
    const minDuration = 10000; // 10 seconds
    const startTime = Date.now();

    images.forEach(src => {
        const img = new Image();
        const onLoad = () => { loaded++; };
        img.onload = onLoad;
        img.onerror = onLoad;
        img.src = src;
    });

    const interval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const timeFraction = elapsed / minDuration;
        const loadFraction = loaded / totalImages;
        const progress = Math.min(timeFraction, loadFraction, 1);
        const percent = Math.round(progress * 100);
        progressBar.style.width = percent + '%';
        percentText.textContent = percent + '%';
        if (progress >= 1 && loaded === totalImages) {
            clearInterval(interval);
            window.location.href = 'welcome.php';
        }
    }, 100);

    fetch('loading_messages.txt')
        .then(r => r.text())
        .then(text => {
            const lines = text.split('\n').filter(Boolean);
            const message = lines[0] || '';
            let index = 0;
            function typeChar() {
                if (index < message.length) {
                    messageElem.textContent += message[index];
                    index++;
                    setTimeout(typeChar, 80);
                } else {
                    messageElem.style.borderRight = 'none';
                }
            }
            typeChar();
        });
});