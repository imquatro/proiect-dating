// Array de poze, array de comentarii, etc.
const galleryPhotos = <?= json_encode($poze) ?>;
const galleryDates = <?= json_encode(array_map(function($src){ return date('Y-m-d H:i', filemtime(__DIR__.'/'.$src)); }, $poze)) ?>;
// Exemplu: like/comment info: (în real life, preiei din DB)
const likesArr = Array(galleryPhotos.length).fill(0); // sau preiei din DB

let vpIdx = 0;
const vpLightbox = document.getElementById('vpLightbox');
const vpLightboxImg = document.getElementById('vpLightboxImg');
const vpLbPrevBtn = document.getElementById('vpLbPrevBtn');
const vpLbNextBtn = document.getElementById('vpLbNextBtn');
const vpPhotoDate = document.getElementById('vpPhotoDate');
const vpPhotoLikes = document.getElementById('vpPhotoLikes');
const vpPhotoCommentsCount = document.getElementById('vpPhotoCommentsCount');
const vpPhotoComments = document.getElementById('vpPhotoComments');
const vpCommentForm = document.getElementById('vpCommentForm');

const fakeComments = [
  // Exemplu: în real life, fetch din DB AJAX
  [{user:"Marius",avatar:"default-avatar.jpg",text:"Salut! Foarte tare poza."}],
  [{user:"Andreea",avatar:"default-avatar.jpg",text:"Super!"}]
];

function openLightbox(idx=0) {
  vpIdx = idx;
  updateVpLightbox();
  vpLightbox.classList.add('active');
}
function closeVpLightbox() {
  vpLightbox.classList.remove('active');
}
function updateVpLightbox() {
  vpLightboxImg.src = galleryPhotos[vpIdx] ?? '';
  vpPhotoDate.textContent = galleryDates[vpIdx] ?? '';
  vpPhotoLikes.textContent = likesArr[vpIdx] ?? 0;
  // Comentarii fake demo
  let coms = fakeComments[vpIdx] ?? [];
  vpPhotoComments.innerHTML = coms.map(c =>
    `<div class="vp-comment-row">
      <img src="${c.avatar}" class="vp-comment-avatar" />
      <div class="vp-comment-content">
        <span class="vp-comment-username">${c.user}</span>
        ${c.text}
      </div>
    </div>`
  ).join('');
  vpPhotoCommentsCount.textContent = coms.length;
}
vpLbPrevBtn.onclick = function() {
  if(vpIdx>0) { openLightbox(vpIdx-1); }
}
vpLbNextBtn.onclick = function() {
  if(vpIdx<galleryPhotos.length-1) { openLightbox(vpIdx+1); }
}
window.closeVpLightbox = closeVpLightbox;
document.addEventListener('keydown', function(e){
  if (!vpLightbox.classList.contains('active')) return;
  if (e.key==='Escape') closeVpLightbox();
  if (e.key==='ArrowLeft') vpLbPrevBtn.click();
  if (e.key==='ArrowRight') vpLbNextBtn.click();
});
if (vpCommentForm) {
  vpCommentForm.onsubmit = function(e){
    e.preventDefault();
    let val = this.comment.value.trim();
    if(val) {
      // În real life: POST AJAX!
      if(!fakeComments[vpIdx]) fakeComments[vpIdx]=[];
      fakeComments[vpIdx].push({user:'Eu',avatar:'default-avatar.jpg',text:val});
      this.comment.value = '';
      updateVpLightbox();
    }
  }
}
