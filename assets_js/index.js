document.addEventListener('DOMContentLoaded', () => {
    const frame = document.getElementById('welcome-frame');
    const startButton = document.getElementById('start-button');
    const authContainer = document.getElementById('auth-container');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showRegister = document.getElementById('show-register');
    const showLogin = document.getElementById('show-login');

    if (frame) {
        frame.classList.add('drop');
    }

    if (startButton) {
        startButton.addEventListener('click', () => {
            frame.style.display = 'none';
            authContainer.classList.remove('hidden');
        });
    }

    if (showRegister) {
        showRegister.addEventListener('click', (e) => {
            e.preventDefault();
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
        });
    }

    if (showLogin) {
        showLogin.addEventListener('click', (e) => {
            e.preventDefault();
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        });
    }
});