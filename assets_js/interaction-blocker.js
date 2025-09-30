document.addEventListener('DOMContentLoaded', () => {
    // Disable context menu (right-click)
    document.addEventListener('contextmenu', e => e.preventDefault());

    // Prevent dragging of images
    document.addEventListener('dragstart', e => {
        if (e.target.tagName.toLowerCase() === 'img') {
            e.preventDefault();
        }
    });

    // Prevent text selection except in form fields
    document.addEventListener('selectstart', e => {
        const tag = e.target.tagName.toLowerCase();
        if (tag !== 'input' && tag !== 'textarea' && tag !== 'select') {
            e.preventDefault();
        }
    });
});