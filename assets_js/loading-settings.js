// Loading Settings Page Script with Live Preview

document.addEventListener('DOMContentLoaded', () => {
    const styleCards = document.querySelectorAll('.loading-style-card');
    const applyBtns = document.querySelectorAll('.apply-style-btn');
    const saveBtn = document.getElementById('saveStyleBtn');
    const messageEl = document.getElementById('styleMessage');
    const bgColorPicker = document.getElementById('previewBgColor');
    const previewAreas = document.querySelectorAll('.live-preview-area');
    
    let selectedVariant = document.querySelector('.loading-style-card.active')?.dataset.variant || 'variant-1';
    let animationIntervals = new Map();

    // Load saved background color
    const savedBgColor = localStorage.getItem('previewBgColor') || '#87CEEB';
    bgColorPicker.value = savedBgColor;
    applyBackgroundColor(savedBgColor);

    // Handle background color change
    bgColorPicker.addEventListener('input', (e) => {
        const color = e.target.value;
        applyBackgroundColor(color);
        localStorage.setItem('previewBgColor', color);
    });

    function applyBackgroundColor(color) {
        previewAreas.forEach(area => {
            area.style.background = color;
        });
    }

    // Handle style card selection (visual preview only, NOT apply)
    styleCards.forEach(card => {
        card.addEventListener('click', (e) => {
            // Don't trigger if clicking apply button
            if (e.target.closest('.apply-style-btn')) return;
            
            // Remove selected class from all cards
            styleCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected to clicked card (not active - active is for applied style)
            card.classList.add('selected');
            
            // Update selected variant
            selectedVariant = card.dataset.variant;
            
            // Start animation for this card
            startAnimation(card);
        });
    });

    // Handle Apply button clicks
    applyBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const variant = btn.dataset.variant;
            
            try {
                const response = await fetch('save_loading_preference.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        loading_style: variant
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update localStorage for immediate effect
                    localStorage.setItem('loadingStyle', variant);
                    
                    // Remove active from all cards
                    styleCards.forEach(c => c.classList.remove('active'));
                    
                    // Add active ONLY to the applied card
                    btn.closest('.loading-style-card').classList.add('active');
                    
                    showMessage('Loading style applied and saved! ✓', 'success');
                } else {
                    showMessage(data.message || 'Failed to apply style', 'error');
                }
            } catch (error) {
                console.error('Error applying style:', error);
                showMessage('An error occurred while applying', 'error');
            }
        });
    });

    // Save button (redirects back to settings)
    saveBtn.addEventListener('click', () => {
        window.location.href = 'settings.php';
    });

    // Animate progress bars in loop
    function startAnimation(card) {
        const progressBar = card.querySelector('.nav-progress-bar-demo');
        const percentText = card.querySelector('.nav-percent-demo');
        
        if (!progressBar || !percentText) return;
        
        // Clear existing animation for this card
        if (animationIntervals.has(card)) {
            clearInterval(animationIntervals.get(card));
        }
        
        let progress = 0;
        const interval = setInterval(() => {
            progress += 2;
            if (progress > 100) progress = 0;
            
            progressBar.style.width = progress + '%';
            percentText.textContent = progress + '%';
        }, 50);
        
        animationIntervals.set(card, interval);
    }

    // Start animations for all cards initially
    styleCards.forEach(card => {
        startAnimation(card);
    });

    function showMessage(text, type) {
        messageEl.textContent = text;
        messageEl.className = `style-message ${type} show`;
        
        setTimeout(() => {
            messageEl.classList.remove('show');
        }, 3000);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        animationIntervals.forEach(interval => clearInterval(interval));
    });
});
