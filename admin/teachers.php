<?php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Handle create/edit/delete actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $reg_no = strtoupper(trim($_POST['reg_no']));
        $name = trim($_POST['name']);
        $password = $_POST['password'] ?? '';

        // enforce prefix TCH
        if (!preg_match('/^TCH[0-9]+$/', $reg_no)) {
            $message = 'Teacher reg_no must start with TCH followed by digits, e.g. TCH1001.';
            $messageType = 'error';
        } elseif (empty($name) || strlen($password) < PASSWORD_MIN_LENGTH) {
            $message = 'Please provide valid name and a password (min ' . PASSWORD_MIN_LENGTH . ' chars).';
            $messageType = 'error';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO teachers (reg_no, name, password_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $reg_no, $name, $password_hash);
            if ($stmt->execute()) {
                $message = 'Teacher created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error creating teacher: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare('DELETE FROM teachers WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $message = 'Teacher deleted.';
            $messageType = 'success';
        } else {
            $message = 'Error deleting teacher: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'reset_password' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $newpw = $_POST['new_password'] ?? '';
        if (strlen($newpw) < PASSWORD_MIN_LENGTH) {
            $message = 'New password is too short.';
            $messageType = 'error';
        } else {
            $hash = password_hash($newpw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE teachers SET password_hash = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $id);
            if ($stmt->execute()) {
                $message = 'Password reset successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error resetting password: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
}

// Fetch teachers
$teachers_query = "SELECT t.*, 
                  (SELECT COUNT(*) FROM teacher_assignments ta WHERE ta.teacher_id = t.id AND ta.status = 'approved') as subject_count 
                  FROM teachers t 
                  ORDER BY t.id DESC";
$teachers = $conn->query($teachers_query);

$page_title = 'Manage Teachers';
include __DIR__ . '/../includes/header.php';
?>

<!-- Admin Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 flex-1 p-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Teachers</h1>
            <p class="text-gray-600">Manage your teaching staff</p>
        </div>
        <button onclick="document.getElementById('addTeacherModal').classList.remove('hidden')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center shadow-md transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Teacher
        </button>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php if ($messageType === 'success'): ?>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <?php else: ?>
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?php endif; ?>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Teachers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php while ($t = $teachers->fetch_assoc()): ?>
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-200 overflow-hidden border border-gray-100">
                <div class="p-6 text-center">
                    <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-600 text-2xl font-bold">
                        <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($t['name']); ?></h3>
                    <p class="text-sm text-gray-500 mb-4"><?php echo htmlspecialchars($t['reg_no']); ?></p>
                    
                    <div class="flex justify-center items-center space-x-2 mb-4">
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                            <?php echo $t['subject_count']; ?> Subjects
                        </span>
                    </div>

                    <div class="flex justify-center space-x-3 pt-4 border-t border-gray-100">
                        <button onclick="toggleResetModal(<?php echo $t['id']; ?>)" class="text-yellow-600 hover:text-yellow-700 text-sm font-medium">Reset PW</button>
                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this teacher?');">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>" />
                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">Delete</button>
                        </form>
                    </div>
                    
                    <!-- Reset PW Form (Hidden by default) -->
                    <div id="reset-form-<?php echo $t['id']; ?>" class="hidden mt-4 pt-4 border-t border-gray-100">
                         <form method="post" class="flex flex-col space-y-2">
                             <input type="hidden" name="action" value="reset_password" />
                             <input type="hidden" name="id" value="<?php echo $t['id']; ?>" />
                             <input type="password" name="new_password" placeholder="New Password" class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:border-blue-500" required />
                             <button type="submit" class="bg-yellow-500 text-white text-xs px-2 py-1 rounded hover:bg-yellow-600">Save Password</button>
                         </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add Teacher Modal -->
<div id="addTeacherModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New Teacher</h3>
            <form method="post">
                <input type="hidden" name="action" value="create" />
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration No</label>
                        <input name="reg_no" placeholder="e.g. TCH1001" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input name="name" placeholder="John Doe" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input name="password" type="password" placeholder="******" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                    </div>
                </div>
                <div class="items-center px-4 py-3 mt-4 text-right">
                    <button type="button" onclick="document.getElementById('addTeacherModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleResetModal(id) {
    const form = document.getElementById('reset-form-' + id);
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
    } else {
        form.classList.add('hidden');
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
