const superPanelImages = <?=json_encode($view_poze)?>;
const superPanelComments = <?=json_encode($comments)?>;
const superPanelDate = "<?=htmlspecialchars($data_pozei)?>";
const superPanelLikes = <?=$likes?>;
let superPanelIdx = 0;

function openSuperPanel(idx) {
  superPanelIdx = idx;
  updateSuperPanel();
  document.getElementById('vpComboPanelOverlay').style.display = 'flex';
}
function vpCloseComboPanel() {
  document.getElementById('vpComboPanelOverlay').style.display = 'none';
}
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
function updateSuperPanel() {
  document.getElementById('vpPanelPhoto').src = superPanelImages[superPanelIdx] ?? 'default-avatar.jpg';
  document.getElementById('vpMetaDate').textContent = superPanelDate;
  document.getElementById('vpMetaLikes').textContent = superPanelLikes;
  document.getElementById('vpMetaComments').textContent = superPanelComments.length;
  let cdiv = document.getElementById('vpCommentsWrap');
  cdiv.innerHTML = '';
  for (let c of superPanelComments) {
    cdiv.innerHTML += `<div class="vp-comment-row"><span class="vp-comment-username">${c.username}:</span><span class="vp-comment-text">${c.text}</span></div>`;
  }
}

// Închidere la click pe fundal overlay (opțional)
document.getElementById('vpComboPanelOverlay').addEventListener('click', function(e){
  if(e.target === this) vpCloseComboPanel();
});
