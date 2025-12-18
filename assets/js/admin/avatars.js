// assets/js/admin/avatars.js
// Client-side preview for avatar uploads

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="file"][name="avatar"]').forEach(function (input) {
        const preview = document.createElement('div');
        preview.style.display = 'inline-block';
        preview.style.marginLeft = '8px';
        input.parentNode.insertBefore(preview, input.nextSibling);

        input.addEventListener('change', function () {
            preview.innerHTML = '';
            const f = input.files[0];
            if (!f) return;
            if (f.size > 1 * 1024 * 1024) {
                const msg = document.createElement('div');
                msg.style.color = 'red';
                msg.textContent = 'Avatar must be 1MB or smaller.';
                preview.appendChild(msg);
                input.value = '';
                return;
            }
            const img = document.createElement('img');
            img.style.width = '64px';
            img.style.height = '64px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '50%';
            preview.appendChild(img);
            const reader = new FileReader();
            reader.onload = function (ev) { img.src = ev.target.result; }
            reader.readAsDataURL(f);
        });
    });
});