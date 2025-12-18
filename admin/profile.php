<?php
// admin/profile.php
require_once '../includes/middleware/admin_auth.php';
require_once '../config/database.php';

$conn = getDBConnection();
$admin_id = $_SESSION['user_id'];

// Handle name change
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['display_name'])) {
        $display_name = trim($_POST['display_name']);
        $stmt = $conn->prepare('UPDATE admins SET username = ? WHERE id = ?');
        $stmt->bind_param('si', $display_name, $admin_id);
        if ($stmt->execute()) {
            $message = 'Profile updated.';
            $_SESSION['username'] = $display_name;
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare('SELECT id, username, avatar FROM admins WHERE id = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$page_title = 'Admin Profile';
include '../includes/header.php';
?>

<div class="max-w-3xl mx-auto py-8">
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-xl font-semibold mb-4">Profile</h2>
        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded-md bg-green-100 text-green-800"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post" action="profile.php" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Display Name</label>
                <input type="text" name="display_name" value="<?php echo htmlspecialchars($admin['username']); ?>" class="mt-1 block w-full border border-gray-300 px-3 py-2 rounded-md" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Change Profile Picture</label>
                <form action="../includes/upload_avatar.php" method="post" enctype="multipart/form-data" class="mt-2">
                    <input type="file" name="avatar" accept="image/*" required />
                    <button type="submit" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">Upload</button>
                </form>
            </div>

            <div>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
