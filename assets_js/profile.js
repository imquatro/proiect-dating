function toggleDescEdit() {
    const editDiv = document.getElementById('desc-edit-div');
    const viewDiv = document.getElementById('desc-view-div');
    if (!editDiv || !viewDiv) return;
    const showEdit = editDiv.style.display === 'none' || editDiv.style.display === '';
    editDiv.style.display = showEdit ? 'block' : 'none';
    viewDiv.style.display = showEdit ? 'none' : 'block';
}

function initProfile() {
    const selectBtn = document.getElementById('select-btn');
    const input = document.getElementById('profile-photo-input');
    const uploadBtn = document.getElementById('upload-btn');
    if (selectBtn && input && uploadBtn) {
        selectBtn.addEventListener('click', () => input.click());
        input.addEventListener('change', () => {
            uploadBtn.style.display = input.files.length > 0 ? 'inline-block' : 'none';
        });
    }
    const descEditBtn = document.getElementById('descEditBtn');
    if (descEditBtn) {
        descEditBtn.addEventListener('click', toggleDescEdit);
    }
}

document.addEventListener('DOMContentLoaded', initProfile);