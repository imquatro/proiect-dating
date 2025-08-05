// Toggle vizibilitate container galerii mici
document.getElementById('pending-galleries-alert')?.addEventListener('click', () => {
    const container = document.getElementById('pending-galleries-container');
    container.style.display = container.style.display === 'none' ? 'block' : 'none';
});

// Variabile pentru marire poza
let currentZoomUserId = null;
let currentZoomIndex = 0;
let allPhotosByUser = {};

// Construim un map userID => lista poze
document.querySelectorAll('.small-photo-wrapper').forEach(el => {
    const userId = el.dataset.userid;
    if (!allPhotosByUser[userId]) allPhotosByUser[userId] = [];
    allPhotosByUser[userId].push(el.querySelector('img').src);
});

// Elemente DOM
const zoomOverlay = document.getElementById('photo-zoom-overlay');
const zoomedPhoto = document.getElementById('zoomed-photo');
const zoomCloseBtn = document.getElementById('zoom-close-btn');
const zoomPrevBtn = document.getElementById('zoom-prev-btn');
const zoomNextBtn = document.getElementById('zoom-next-btn');

// Arată poza mărită
function showZoomedPhoto(userId, index) {
    currentZoomUserId = userId;
    currentZoomIndex = index;
    zoomedPhoto.src = allPhotosByUser[userId][index];
    zoomOverlay.style.display = 'flex';
    updateZoomButtons();
}

// Actualizează starea butoanelor stânga/dreapta
function updateZoomButtons() {
    zoomPrevBtn.disabled = currentZoomIndex === 0;
    zoomNextBtn.disabled = currentZoomIndex === allPhotosByUser[currentZoomUserId].length - 1;
}

// Evenimente click pe butoanele lupă din galeriile mici
document.querySelectorAll('.small-photo-wrapper .zoom-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const wrapper = e.target.closest('.small-photo-wrapper');
        const userId = wrapper.dataset.userid;
        const index = parseInt(wrapper.dataset.index);
        showZoomedPhoto(userId, index);
    });
});

// Navigare poze mărite
zoomPrevBtn.addEventListener('click', () => {
    if (currentZoomIndex > 0) {
        currentZoomIndex--;
        showZoomedPhoto(currentZoomUserId, currentZoomIndex);
    }
});
zoomNextBtn.addEventListener('click', () => {
    if (currentZoomIndex < allPhotosByUser[currentZoomUserId].length - 1) {
        currentZoomIndex++;
        showZoomedPhoto(currentZoomUserId, currentZoomIndex);
    }
});

// Închidere overlay la click pe butonul X
zoomCloseBtn.addEventListener('click', () => {
    zoomOverlay.style.display = 'none';
    zoomedPhoto.src = '';
});

// Închidere overlay la click pe zona neagră din jur
zoomOverlay.addEventListener('click', (e) => {
    if (e.target === zoomOverlay) {
        zoomOverlay.style.display = 'none';
        zoomedPhoto.src = '';
    }
});
