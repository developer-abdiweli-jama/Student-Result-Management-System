<?php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign') {
        $teacher_id = (int)$_POST['teacher_id'];
        $subject_id = (int)$_POST['subject_id'];
        $class_level = trim($_POST['class_level']);

        if (empty($teacher_id) || empty($subject_id) || empty($class_level)) {
            $message = 'Please fill all fields.';
            $messageType = 'error';
        } else {
            // Check existing assignments count
            $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_assignments WHERE teacher_id = ? AND status = 'approved'");
            $countStmt->bind_param('i', $teacher_id);
            $countStmt->execute();
            $currCount = $countStmt->get_result()->fetch_assoc()['count'];
            $countStmt->close();

            if ($currCount >= 4) {
                $message = 'Error: This teacher is already assigned to 4 courses. Max limit reached.';
                $messageType = 'error';
            } else {
                // Check if already assigned same subject to same class
                $checkStmt = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND class_level = ?");
                $checkStmt->bind_param('iis', $teacher_id, $subject_id, $class_level);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                     $message = 'Error: Assignment already exists.';
                     $messageType = 'error';
                } else {
                    $status = 'approved';
                    $stmt = $conn->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id, class_level, status) VALUES (?, ?, ?, ?)');
                    $stmt->bind_param('iiss', $teacher_id, $subject_id, $class_level, $status);
                    if ($stmt->execute()) {
                        $message = 'Assignment created successfully.';
                        $messageType = 'success';
                    } else {
                        $message = 'Error: ' . $stmt->error;
                        $messageType = 'error';
                    }
                    $stmt->close();
                }
                $checkStmt->close();
            }
        }
    } elseif ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare('DELETE FROM teacher_assignments WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $message = 'Assignment deleted.';
            $messageType = 'success';
        } else {
            $message = 'Error deleting assignment: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    } elseif ($action === 'approve' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        // Check limits before approving? 
        // Logic: Get teacher_id from assignment
        $infoStmt = $conn->prepare("SELECT teacher_id FROM teacher_assignments WHERE id = ?");
        $infoStmt->bind_param('i', $id);
        $infoStmt->execute();
        $res = $infoStmt->get_result()->fetch_assoc();
        $infoStmt->close();
        
        if ($res) {
            $teacher_id = $res['teacher_id'];
            $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_assignments WHERE teacher_id = ? AND status = 'approved'");
            $countStmt->bind_param('i', $teacher_id);
            $countStmt->execute();
            $currCount = $countStmt->get_result()->fetch_assoc()['count'];
            $countStmt->close(); 
            
            if ($currCount >= 4) {
                 $message = 'Cannot approve: Teacher already has 4 approved courses.';
                 $messageType = 'error';
            } else {
                $stmt = $conn->prepare("UPDATE teacher_assignments SET status = 'approved' WHERE id = ?");
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    $message = 'Request approved.';
                    $messageType = 'success';
                } else {
                    $message = 'Error approving: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'reject' && isset($_POST['id'])) {
         $id = (int)$_POST['id'];
         $stmt = $conn->prepare("UPDATE teacher_assignments SET status = 'rejected' WHERE id = ?");
         $stmt->bind_param('i', $id);
         if ($stmt->execute()) {
             $message = 'Request rejected.';
             $messageType = 'success';
         } else {
             $message = 'Error rejecting: ' . $stmt->error;
             $messageType = 'error';
         }
         $stmt->close();
    }
}

// Fetch Data
$teachers = $conn->query('SELECT id, reg_no, name FROM teachers ORDER BY name');
$subjects = $conn->query('SELECT id, subject_code, subject_name, class_level FROM subjects ORDER BY class_level, subject_code');

// Approved Assignments
$assignments_query = "SELECT ta.id, t.reg_no, t.name, s.subject_code, s.subject_name, ta.class_level 
                     FROM teacher_assignments ta 
                     JOIN teachers t ON ta.teacher_id = t.id 
                     JOIN subjects s ON ta.subject_id = s.id 
                     WHERE ta.status = 'approved'
                     ORDER BY t.name";
$assignments = $conn->query($assignments_query);

// Pending Requests
$pending_query = "SELECT ta.id, t.reg_no, t.name, s.subject_code, s.subject_name, ta.class_level 
                 FROM teacher_assignments ta 
                 JOIN teachers t ON ta.teacher_id = t.id 
                 JOIN subjects s ON ta.subject_id = s.id 
                 WHERE ta.status = 'pending'
                 ORDER BY taal.created_at ASC"; // Fixed alias typo in query string if needed, ta is alias
// Fix order by
$pending_query = "SELECT ta.id, t.reg_no, t.name, s.subject_code, s.subject_name, ta.class_level, ta.created_at
                 FROM teacher_assignments ta 
                 JOIN teachers t ON ta.teacher_id = t.id 
                 JOIN subjects s ON ta.subject_id = s.id 
                 WHERE ta.status = 'pending'
                 ORDER BY ta.created_at ASC";
$pending_assignments = $conn->query($pending_query);


$page_title = 'Teacher Assignments';
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
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Teacher Assignments</h1>
                <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                    <span>Admin</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span>Management</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600 font-bold">Assignments</span>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-blue-50 text-blue-600 rounded-xl border border-blue-100 flex items-center gap-2">
                    <i class="fas fa-info-circle text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">Max 4 Courses Per Teacher</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 <?php echo $messageType === 'success' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'; ?> animate-fade-in-up">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <span class="font-bold text-sm"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Manual Assignment Form -->
            <div class="lg:col-span-1">
                <div class="dashboard-card sticky top-32">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/20">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">New Assignment</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Connect Teacher to Subject</p>
                        </div>
                    </div>

                    <form method="post" class="space-y-6">
                        <input type="hidden" name="action" value="assign" />
                        
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Teacher</label>
                            <div class="relative">
                                <i class="fas fa-chalkboard-teacher absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select name="teacher_id" required class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                                    <option value="">Select teacher</option>
                                    <?php while ($t = $teachers->fetch_assoc()): ?>
                                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name'] . ' (' . $t['reg_no'] . ')'); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Class Level</label>
                            <div class="relative">
                                <i class="fas fa-layer-group absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select name="class_level" required class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                                     <option value="">Select Class...</option>
                                     <option value="Form 1">Form 1</option>
                                     <option value="Form 2">Form 2</option>
                                     <option value="Form 3">Form 3</option>
                                     <option value="Form 4">Form 4</option>
                                     <option value="Grade 5">Grade 5</option>
                                     <option value="Grade 6">Grade 6</option>
                                     <option value="Grade 7">Grade 7</option>
                                     <option value="Grade 8">Grade 8</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Subject</label>
                            <div class="relative">
                                <i class="fas fa-book absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select name="subject_id" required disabled class="w-full pl-11 pr-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-400 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer">
                                     <option value="">Select Level First</option>
                                    <?php while ($s = $subjects->fetch_assoc()): ?>
                                        <option value="<?php echo $s['id']; ?>" data-class-level="<?php echo htmlspecialchars($s['class_level']); ?>">
                                            <?php echo htmlspecialchars($s['subject_name'] . ' (' . $s['subject_code'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <button type="submit" class="premium-btn w-full py-4 rounded-xl text-xs font-black uppercase tracking-widest shadow-xl shadow-blue-500/20 hover:shadow-blue-500/40 hover:-translate-y-1 active:scale-95 transition-all">
                            Assign Subject
                        </button>
                    </form>
                </div>
            </div>

        <script>
        document.querySelector('select[name="class_level"]').addEventListener('change', function() {
            const classLevel = this.value;
            const subjectSelect = document.querySelector('select[name="subject_id"]');
            const options = subjectSelect.querySelectorAll('option');
            
            if (classLevel) {
                subjectSelect.disabled = false;
                subjectSelect.classList.remove('bg-slate-100', 'text-slate-400');
                subjectSelect.classList.add('bg-slate-50', 'text-slate-900');
            } else {
                subjectSelect.disabled = true;
                subjectSelect.classList.add('bg-slate-100', 'text-slate-400');
                subjectSelect.classList.remove('bg-slate-50', 'text-slate-900');
            }

            options.forEach(option => {
                if (option.value === "") {
                    option.textContent = classLevel ? "Select subject for " + classLevel : "Select Level First";
                    return;
                }
                
                const optionClass = option.getAttribute('data-class-level');
                option.style.display = (optionClass === classLevel) ? "" : "none";
            });
            subjectSelect.value = "";
        });
        </script>

        <!-- Lists -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Pending Requests -->
            <?php if ($pending_assignments->num_rows > 0): ?>
            <div class="dashboard-card p-0 overflow-hidden border-orange-100 ring-4 ring-orange-50/50">
                <div class="px-8 py-6 bg-orange-50/50 border-b border-orange-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-orange-900 uppercase tracking-widest">Pending Requests</h3>
                        <p class="text-[10px] font-bold text-orange-400 uppercase tracking-widest mt-0.5">Awaiting Administrator Approval</p>
                    </div>
                    <span class="bg-orange-100 text-orange-600 text-[10px] font-black px-3 py-1 rounded-full border border-orange-200"><?php echo $pending_assignments->num_rows; ?> NEW</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern w-full">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Teacher</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Subject Details</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while ($p = $pending_assignments->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-black text-xs border border-orange-100">
                                                <?php echo substr($p['name'], 0, 1); ?>
                                            </div>
                                            <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($p['name']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($p['subject_name']); ?></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] font-black text-slate-400 uppercase"><?php echo htmlspecialchars($p['subject_code']); ?></span>
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span class="text-[10px] font-black text-blue-500 uppercase"><?php echo htmlspecialchars($p['class_level']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-right">
                                        <form method="post" class="inline-flex gap-2">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>" />
                                            <button type="submit" name="action" value="approve" 
                                                    class="h-9 px-4 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs hover:bg-emerald-600 hover:text-white transition-all border border-emerald-100 shadow-sm">
                                                Approve
                                            </button>
                                            <button type="submit" name="action" value="reject" 
                                                    class="h-9 px-4 rounded-lg bg-rose-50 text-rose-600 font-bold text-xs hover:bg-rose-600 hover:text-white transition-all border border-rose-100 shadow-sm">
                                                Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Approved Assignments -->
            <div class="dashboard-card p-0 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/30 flex justify-between items-center">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Active Assignments</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Approved teaching staff allocations</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern w-full">
                        <thead class="bg-slate-50/50 text-slate-400">
                            <tr>
                                <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest">Teacher</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest">Subject Details</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/50">
                            <?php if ($assignments->num_rows === 0): ?>
                                <tr>
                                    <td colspan="3" class="px-8 py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="fas fa-folder-open text-2xl opacity-20"></i>
                                            <span class="text-xs font-bold">No active assignments found</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($a = $assignments->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors group">
                                        <td class="px-8 py-5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-black text-xs border border-blue-100">
                                                    <?php echo substr($a['name'], 0, 1); ?>
                                                </div>
                                                <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($a['name']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap">
                                            <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($a['subject_name']); ?></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-black text-slate-400 uppercase"><?php echo htmlspecialchars($a['subject_code']); ?></span>
                                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                <span class="text-[10px] font-black text-indigo-500 uppercase"><?php echo htmlspecialchars($a['class_level'] ?? 'N/A'); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-8 py-5 whitespace-nowrap text-right">
                                            <form method="post" onsubmit="return confirm('Remove this assignment?');" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                                <input type="hidden" name="action" value="delete" />
                                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>" />
                                                <button type="submit" class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
