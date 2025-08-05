document.addEventListener('DOMContentLoaded', () => {
    const frame = document.getElementById('welcome-frame');
    const startButton = document.getElementById('start-button');

    frame.classList.add('drop');

    if (startButton) {
        startButton.addEventListener('click', () => {
            window.location.href = 'welcome.php';
        });
    }
});