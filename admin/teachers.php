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
<div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
    <!-- Glass Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Teacher Management</h1>
                <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                    <span>Admin</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600 font-bold">Faculty</span>
                </nav>
            </div>
            <button onclick="document.getElementById('addTeacherModal').classList.remove('hidden')" 
                    class="premium-btn flex items-center gap-2 text-xs">
                <i class="fas fa-plus"></i>
                <span>Add Teacher</span>
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'; ?> animate-fade-in-up">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <!-- Teachers Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            <?php while ($t = $teachers->fetch_assoc()): ?>
                <div class="dashboard-card group hover:-translate-y-1 transition-transform duration-300">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-4 relative">
                            <div class="absolute inset-0 bg-blue-600 rounded-2xl rotate-6 opacity-10 group-hover:rotate-12 transition-transform"></div>
                            <div class="relative w-full h-full bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-2xl font-black text-blue-600">
                                <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                            </div>
                        </div>
                        
                        <h3 class="text-lg font-black text-slate-900 mb-1"><?php echo htmlspecialchars($t['name']); ?></h3>
                        <span class="inline-block px-3 py-1 bg-slate-100 text-slate-500 rounded-lg text-[10px] font-bold uppercase tracking-widest mb-4">
                            <?php echo htmlspecialchars($t['reg_no']); ?>
                        </span>
                        
                        <div class="grid grid-cols-1 gap-2 mb-6">
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="block text-2xl font-black text-slate-900"><?php echo $t['subject_count']; ?></span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Assigned Subjects</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-2 pt-4 border-t border-slate-50">
                            <button onclick="toggleResetModal(<?php echo $t['id']; ?>)" 
                                    class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-500 hover:text-white transition-all shadow-sm"
                                    title="Reset Password">
                                <i class="fas fa-key text-xs"></i>
                            </button>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this teacher? This will preserve their historical data linked to results but remove their access.');">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?php echo $t['id']; ?>" />
                                <button type="submit" 
                                        class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm"
                                        title="Delete Account">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                        
                        <!-- Reset PW Form (Hidden by default) -->
                        <div id="reset-form-<?php echo $t['id']; ?>" class="hidden mt-4 pt-4 border-t border-slate-50 animate-fade-in-up">
                             <form method="post" class="space-y-2">
                                 <input type="hidden" name="action" value="reset_password" />
                                 <input type="hidden" name="id" value="<?php echo $t['id']; ?>" />
                                 <input type="password" name="new_password" placeholder="New Password" 
                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all placeholder:text-slate-400" required />
                                 <button type="submit" class="w-full py-2 bg-amber-500 text-white rounded-lg text-xs font-bold hover:bg-amber-600 transition-all shadow-sm">
                                     Update Access
                                 </button>
                             </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Add Teacher Modal -->
<div id="addTeacherModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-blue-50 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-black leading-6 text-slate-900" id="modal-title">New Faculty Member</h3>
                        <div class="mt-2">
                            <p class="text-sm text-slate-500 mb-6">Create a new teacher account. Registration number must follow the TCH format.</p>
                            
                            <form method="post" class="space-y-4">
                                <input type="hidden" name="action" value="create" />
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Registration ID</label>
                                    <input name="reg_no" placeholder="e.g. TCH1001" 
                                           class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Full Name</label>
                                    <input name="name" placeholder="John Doe" 
                                           class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300" required />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Initial Password</label>
                                    <input name="password" type="password" placeholder="******" 
                                           class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300" required />
                                </div>
                                
                                <div class="mt-8 flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('addTeacherModal').classList.add('hidden')" 
                                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" class="premium-btn px-6 py-2 rounded-xl text-xs font-bold shadow-lg shadow-blue-500/20">
                                        Create Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
