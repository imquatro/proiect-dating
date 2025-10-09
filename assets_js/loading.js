document.addEventListener('DOMContentLoaded', () => {
    const progressBar = document.getElementById('progress-bar');
    const progressTip = document.getElementById('progress-tip');
    const percentText = document.getElementById('progress-text');
    const messageElem = document.getElementById('message');
    const images = JSON.parse(document.getElementById('image-data').textContent);
    const totalImages = images.length || 1;
    let loaded = 0;
    const minDuration = 8000; // 8 seconds
    const startTime = Date.now();

    // Preload images
    images.forEach(src => {
        const img = new Image();
        const onLoad = () => { loaded++; };
        img.onload = onLoad;
        img.onerror = onLoad;
        img.src = src;
    });

    // Modern progress animation
    const interval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const timeFraction = elapsed / minDuration;
        const loadFraction = loaded / totalImages;
        const progress = Math.min(timeFraction, loadFraction, 1);
        const percent = Math.round(progress * 100);
        
        // Update progress bar with smooth animation
        progressBar.style.width = percent + '%';
        progressTip.style.opacity = progress > 0 ? '1' : '0';
        percentText.textContent = percent + '%';

        // No plant animation in this variant
        
        // Add glow effect when progress is high
        if (percent > 80) {
            progressBar.style.boxShadow = '0 0 30px rgba(255, 255, 255, 0.5)';
        }
        
        if (progress >= 1 && loaded === totalImages) {
            clearInterval(interval);
            // Add completion animation
            progressBar.style.animation = 'none';
            progressTip.style.animation = 'none';
            setTimeout(() => {
                window.location.href = 'welcome.php';
            }, 500);
        }
    }, 50);

    // Modern loading messages
    const loadingMessages = [
        "Initializing system...",
        "Loading assets...",
        "Preparing interface...",
        "Almost ready...",
        "Welcome!"
    ];
    
    let messageIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    
    function typeMessage() {
        const currentMessage = loadingMessages[messageIndex];
        
        if (isDeleting) {
            messageElem.textContent = currentMessage.substring(0, charIndex - 1);
            charIndex--;
        } else {
            messageElem.textContent = currentMessage.substring(0, charIndex + 1);
            charIndex++;
        }
        
        let typeSpeed = isDeleting ? 50 : 100;
        
        if (!isDeleting && charIndex === currentMessage.length) {
            typeSpeed = 2000; // Pause at end
            isDeleting = true;
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            messageIndex = (messageIndex + 1) % loadingMessages.length;
            typeSpeed = 500; // Pause before next message
        }
        
        setTimeout(typeMessage, typeSpeed);
    }
    
    // Start typing animation
    typeMessage();
});