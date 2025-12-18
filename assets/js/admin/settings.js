// assets/js/admin/settings.js

function switchTab(tabId) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    // Reset all tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-slate-900', 'text-white');
        btn.classList.add('text-slate-400');
    });

    // Show selected tab content
    document.getElementById(`tab-${tabId}`).classList.remove('hidden');

    // Activate selected tab button
    const activeBtn = document.getElementById(`tab-btn-${tabId}`);
    activeBtn.classList.add('active');
    activeBtn.classList.remove('text-slate-400');
}

document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('logo_input');
    if (!fileInput) return;

    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const logoPreview = document.getElementById('logo_preview');
    const logoPlaceholder = document.getElementById('logo_placeholder');

    fileInput.addEventListener('change', function (e) {
        const file = fileInput.files[0];
        if (!file) return;

        // Update display text
        if (fileNameDisplay) {
            fileNameDisplay.textContent = file.name;
            fileNameDisplay.classList.add('text-blue-600');
        }

        // Preview image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                if (logoPreview) {
                    logoPreview.src = ev.target.result;
                    logoPreview.classList.remove('hidden');
                }
                if (logoPlaceholder) {
                    logoPlaceholder.classList.add('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Form submission confirmation
    const removeLogoForm = document.querySelector('form[action$="delete_logo.php"]');
    if (removeLogoForm) {
        removeLogoForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to remove the current logo?')) {
                e.preventDefault();
            }
        });
    }
});