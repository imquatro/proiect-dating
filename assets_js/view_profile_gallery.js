// assets_js/view_profile_gallery.js

let vpCurrentImg = 0;
let vpTotal = document.querySelectorAll('.vp-photo-wrap').length;

function showVpImg(idx, total) {
    for (let i = 0; i < total; i++) {
        let wrap = document.getElementById('vp-photo-wrap-' + i);
        if (wrap) wrap.style.display = (i === idx) ? 'flex' : 'none';
    }
    document.getElementById('vp-arrow-left').disabled = (idx === 0 || total <= 1);
    document.getElementById('vp-arrow-right').disabled = (idx === total - 1 || total <= 1);
    window.vpCurrentImg = idx;
}
function vpPrevImg(total) {
    if (window.vpCurrentImg > 0) showVpImg(window.vpCurrentImg - 1, total);
}
function vpNextImg(total) {
    if (window.vpCurrentImg < total - 1) showVpImg(window.vpCurrentImg + 1, total);
}
document.addEventListener("DOMContentLoaded", function () {
    window.vpCurrentImg = 0;
    let total = document.querySelectorAll('.vp-photo-wrap').length;
    if (total > 0) showVpImg(0, total);
});

// LIGHTBOX LOGIC
window.openVpLightbox = function(idx) {
    const allPhotos = document.querySelectorAll('.vp-profile-img');
    if (!allPhotos[idx]) return;
    document.getElementById('vpLightboxImg').src = allPhotos[idx].src;
    document.getElementById('vpLightbox').classList.add('active');
    window.vpLbCurrent = idx;
};
window.closeVpLightbox = function() {
    document.getElementById('vpLightbox').classList.remove('active');
};

// Butoane st/dr lightbox
document.addEventListener("DOMContentLoaded", function() {
    let allPhotos = document.querySelectorAll('.vp-profile-img');
    window.vpLbCurrent = 0;
    document.getElementById('vpLbPrevBtn').onclick = function() {
        if (window.vpLbCurrent > 0) {
            window.vpLbCurrent--;
            document.getElementById('vpLightboxImg').src = allPhotos[window.vpLbCurrent].src;
        }
    };
    document.getElementById('vpLbNextBtn').onclick = function() {
        if (window.vpLbCurrent < allPhotos.length - 1) {
            window.vpLbCurrent++;
            document.getElementById('vpLightboxImg').src = allPhotos[window.vpLbCurrent].src;
        }
    };
});
window.closeVpLightbox = closeVpLightbox;
function openVpLightbox(idx) {
    // Ia toate pozele din galerie
    const imgs = document.querySelectorAll('.vp-profile-img');
    const wraps = document.querySelectorAll('.vp-photo-wrap');
    const lightbox = document.getElementById('vpLightbox');
    const lightboxImg = document.getElementById('vpLightboxImg');
    if (!imgs.length || !lightbox || !lightboxImg) return;
    // Pune poza selectată în lightbox
    lightboxImg.src = imgs[idx].src;
    lightbox.classList.add('active');
    // Dacă vrei să reții indexul actual pt navigare stânga-dreapta
    window.vpLightboxIndex = idx;
}
window.openVpLightbox = openVpLightbox; // FOARTE IMPORTANT!
function closeVpLightbox() {
    document.getElementById('vpLightbox').classList.remove('active');
}
window.closeVpLightbox = closeVpLightbox;
