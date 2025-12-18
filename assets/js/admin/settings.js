// assets/js/admin/settings.js
// Client-side preview and checks for site logo upload

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.querySelector('input[name="site_logo"]');
    if (!fileInput) return;

    const maxBytes = 2 * 1024 * 1024; // 2MB
    const previewContainer = document.createElement('div');
    previewContainer.style.marginTop = '8px';
    fileInput.parentNode.insertBefore(previewContainer, fileInput.nextSibling);

    fileInput.addEventListener('change', function (e) {
        previewContainer.innerHTML = '';
        const f = fileInput.files[0];
        if (!f) return;
        if (f.size > maxBytes) {
            const msg = document.createElement('div');
            msg.style.color = 'red';
            msg.textContent = 'File is too large (max 2MB). Please choose a smaller file.';
            previewContainer.appendChild(msg);
            fileInput.value = '';
            return;
        }
        const img = document.createElement('img');
        img.style.maxWidth = '240px';
        img.style.maxHeight = '120px';
        previewContainer.appendChild(img);
        const reader = new FileReader();
        reader.onload = function (ev) { img.src = ev.target.result; }
        reader.readAsDataURL(f);
    });

    // Attach confirmation to the remove logo form if present
    try {
        const removeForm = document.querySelector('form[action$="delete_logo.php"]');
        if (removeForm) {
            removeForm.addEventListener('submit', function (ev) {
                const ok = confirm('Are you sure you want to remove the site logo? This action cannot be undone.');
                if (!ok) ev.preventDefault();
            });
        }
    } catch (e) {
        // ignore
    }
});