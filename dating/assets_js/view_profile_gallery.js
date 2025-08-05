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
// JS pentru panoul lightbox cu imagine sus și comentarii jos
const superPanelImages = <?=json_encode($view_poze)?>;
const superPanelStatus = <?=json_encode($view_gallery_status)?>;
const superPanelComments = <?=json_encode($comments)?>;
const superPanelDate = "<?=htmlspecialchars($data_pozei)?>";
const superPanelLikes = <?=$likes?>;

let superPanelIdx = 0;

// Deschide panou la poza selectată
function openSuperPanel(idx) {
  superPanelIdx = idx;
  updateSuperPanel();
  document.getElementById('vpComboPanelOverlay').style.display = 'flex';
}

// Închide panoul
function vpCloseComboPanel() {
  document.getElementById('vpComboPanelOverlay').style.display = 'none';
}

// Navigare poze în panou
function vpLightboxPrev() {
  if (superPanelIdx > 0) {
    superPanelIdx--;
    updateSuperPanel();
  }
}
function vpLightboxNext() {
  if (superPanelIdx < superPanelImages.length - 1) {
    superPanelIdx++;
    updateSuperPanel();
  }
}

// Actualizare conținut panou la fiecare poză
function updateSuperPanel() {
  // Imagine sus
  document.getElementById('vpPanelPhoto').src = superPanelImages[superPanelIdx] ?? 'default-avatar.jpg';
  // Metadate (data/like)
  document.getElementById('vpMetaDate').textContent = superPanelDate;
  document.getElementById('vpMetaLikes').textContent = superPanelLikes;
  document.getElementById('vpMetaComments').textContent = superPanelComments.length;

  // Comentarii jos
  let cdiv = document.getElementById('vpCommentsWrap');
  cdiv.innerHTML = '';
  for (let c of superPanelComments) {
    cdiv.innerHTML += `<div class="vp-comment-row"><span class="vp-comment-username">${c.username}:</span><span class="vp-comment-text">${c.text}</span></div>`;
  }
}

// Închidere la click pe overlay (optional)
document.getElementById('vpComboPanelOverlay').addEventListener('click', function(e){
  if(e.target === this) vpCloseComboPanel();
});

window.openVpLightbox = openVpLightbox; // FOARTE IMPORTANT!
function closeVpLightbox() {
    document.getElementById('vpLightbox').classList.remove('active');
}
window.closeVpLightbox = closeVpLightbox;
let currentIdx = 0;
function vpPrevImg(total) {
  if (currentIdx > 0) {
    document.getElementById('vp-photo-wrap-' + currentIdx).style.display = 'none';
    currentIdx--;
    document.getElementById('vp-photo-wrap-' + currentIdx).style.display = 'flex';
  }
}
function vpNextImg(total) {
  if (currentIdx < total - 1) {
    document.getElementById('vp-photo-wrap-' + currentIdx).style.display = 'none';
    currentIdx++;
    document.getElementById('vp-photo-wrap-' + currentIdx).style.display = 'flex';
  }
}
