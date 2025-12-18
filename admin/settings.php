<?php
// admin/settings.php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../includes/settings.php';
$page_scripts = ['admin/settings.js'];
require_once __DIR__ . '/../includes/header.php';

$siteName = getSetting('site_name', SITE_NAME);
$siteLogo = getSetting('site_logo', null);

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="max-w-4xl mx-auto p-6">
    <h2 class="text-xl font-semibold mb-4">System Settings</h2>

    <?php if ($flashSuccess): ?>
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?php echo htmlspecialchars($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?php echo htmlspecialchars($flashError); ?></div>
    <?php endif; ?>

    <form method="post" action="settings_save.php" class="mb-6 space-y-4">
        <div>
            <label class="block mb-2 font-medium">Site Name</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($siteName); ?>" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
            <label class="block mb-2 font-medium">Current Academic Year</label>
            <input type="text" name="current_academic_year" value="<?php echo htmlspecialchars(getSetting('current_academic_year', '2024/2025')); ?>" class="w-full border rounded px-3 py-2" placeholder="e.g. 2024/2025" />
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Settings</button>
        </div>
    </form>

    <div class="border-t pt-6">
        <h3 class="text-lg font-medium mb-2">Site Logo</h3>
        <?php if ($siteLogo): ?>
            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($siteLogo); ?>" alt="logo" class="h-20 mb-3 object-contain" />
        <?php endif; ?>

        <form method="post" action="../includes/upload_logo.php" enctype="multipart/form-data">
            <input type="file" name="site_logo" accept="image/png,image/jpeg,image/svg+xml" />
            <div class="mt-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Upload Logo</button>
            </div>
        </form>
        <?php if ($siteLogo): ?>
            <form method="post" action="../includes/delete_logo.php" class="mt-3">
                <button type="submit" class="bg-red-600 text-white px-3 py-2 rounded">Remove Logo</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
