let currentIdx = 0;

const photoPreview = document.getElementById('photoPreview');
const photoIndexInput = document.getElementById('photoIndexInput');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
const lbPrevBtn = document.getElementById('lbPrevBtn');
const lbNextBtn = document.getElementById('lbNextBtn');
const lbApproveBtn = document.getElementById('lbApproveBtn');
const lbRejectBtn = document.getElementById('lbRejectBtn');

function updatePhoto(idx) {
    if (!photos.length) return;
    currentIdx = idx;
    photoPreview.src = photos[currentIdx] ?? '';
    if(photoIndexInput) photoIndexInput.value = currentIdx;
    lightboxImg.src = photos[currentIdx] ?? '';
}

if (prevBtn) prevBtn.onclick = () => {
    if (!photos.length) return;
    currentIdx = (currentIdx - 1 + photos.length) % photos.length;
    updatePhoto(currentIdx);
};
if (nextBtn) nextBtn.onclick = () => {
    if (!photos.length) return;
    currentIdx = (currentIdx + 1) % photos.length;
    updatePhoto(currentIdx);
};

window.openLightbox = function() {
    if (!photos.length) return;
    lightbox.classList.add('active');
    lightboxImg.src = photos[currentIdx] ?? '';
};
window.closeLightbox = function() {
    lightbox.classList.remove('active');
};
if (lbPrevBtn) lbPrevBtn.onclick = () => {
    if (!photos.length) return;
    currentIdx = (currentIdx - 1 + photos.length) % photos.length;
    updatePhoto(currentIdx);
    lightboxImg.src = photos[currentIdx] ?? '';
};
if (lbNextBtn) lbNextBtn.onclick = () => {
    if (!photos.length) return;
    currentIdx = (currentIdx + 1) % photos.length;
    updatePhoto(currentIdx);
    lightboxImg.src = photos[currentIdx] ?? '';
};
if (lbApproveBtn) lbApproveBtn.onclick = () => {
    document.querySelector('#validateForm [name=action][value=approve]').click();
};
if (lbRejectBtn) lbRejectBtn.onclick = () => {
    document.querySelector('#validateForm [name=action][value=reject]').click();
};
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lbPrevBtn.click();
    if (e.key === 'ArrowRight') lbNextBtn.click();
});
document.addEventListener('DOMContentLoaded', function () {
    updatePhoto(0);
});
