<?php
require_once __DIR__ . '/../includes/middleware/teacher_auth.php'; // Using new middleware if exists, but fallback to auth checks
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireTeacher();
$conn = getDBConnection();
$teacher_id = $_SESSION['user_id'];

$message = '';
$messageType = '';

// Handle Subject Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_subject') {
    $subject_id = (int)$_POST['subject_id'];
    $class_level = trim($_POST['class_level']);
    
    // Check if already requested or assigned
    $check = $conn->prepare("SELECT id FROM teacher_assignments WHERE teacher_id = ? AND subject_id = ? AND class_level = ?");
    $check->bind_param('iis', $teacher_id, $subject_id, $class_level);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $message = 'You have already requested or are assigned this subject.';
        $messageType = 'error';
    } else {
        // Validation: Max 3 APPROVED assignments. 
        // Logic: Should we limit REQUESTS too? Maybe just limit approved ones. 
        // But if they have 3 approved, they shouldn't request more.
        $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_assignments WHERE teacher_id = ? AND status = 'approved'");
        $countStmt->bind_param('i', $teacher_id);
        $countStmt->execute();
        $approvedCount = $countStmt->get_result()->fetch_assoc()['count'];
        $countStmt->close();
        
        if ($approvedCount >= 4) {
            $message = 'You already have 4 approved subjects. You cannot request more.';
            $messageType = 'error';
        } else {
            $status = 'pending';
            $stmt = $conn->prepare("INSERT INTO teacher_assignments (teacher_id, subject_id, class_level, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iiss', $teacher_id, $subject_id, $class_level, $status);
            if ($stmt->execute()) {
                $message = 'Request submitted successfully. Waiting for admin approval.';
                $messageType = 'success';
            } else {
                $message = 'Error submitting request: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    }
    $check->close();
}

// Stats
$approved_count = $conn->query("SELECT COUNT(*) as count FROM teacher_assignments WHERE teacher_id = $teacher_id AND status = 'approved'")->fetch_assoc()['count'];
$pending_count = $conn->query("SELECT COUNT(*) as count FROM teacher_assignments WHERE teacher_id = $teacher_id AND status = 'pending'")->fetch_assoc()['count'];

// Fetch Active Assignments
$assignments_query = "SELECT ta.id, s.id AS subject_id, s.subject_code, s.subject_name, ta.class_level 
                     FROM teacher_assignments ta 
                     JOIN subjects s ON ta.subject_id = s.id 
                     WHERE ta.teacher_id = $teacher_id AND ta.status = 'approved' 
                     ORDER BY s.subject_code";
$assignments = $conn->query($assignments_query);

// Fetch Pending Requests
$pending_query = "SELECT ta.id, s.subject_code, s.subject_name, ta.class_level, ta.created_at 
                 FROM teacher_assignments ta 
                 JOIN subjects s ON ta.subject_id = s.id 
                 WHERE ta.teacher_id = $teacher_id AND ta.status = 'pending' 
                 ORDER BY ta.created_at DESC";
$pending_requests = $conn->query($pending_query);

// All Subjects for Dropdown
$all_subjects = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_name");

$page_title = 'Teacher Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<?php include '../includes/teacher_sidebar.php'; ?>

<div class="lg:ml-64 flex-1 bg-[#f8fafc] min-h-screen">
    <!-- Teacher Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Teacher Dashboard</h1>
                <p class="text-slate-500 font-medium flex items-center gap-2 mt-1">
                    <span class="bg-blue-600 w-2 h-2 rounded-full animate-pulse"></span>
                    Welcome back, <span class="text-slate-900 font-bold"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </p>
            </div>
            
            <?php if ($approved_count < 4): ?>
            <button onclick="document.getElementById('requestModal').classList.remove('hidden')" 
                    class="premium-gradient-bg text-white px-6 py-3 rounded-2xl shadow-lg shadow-blue-200 text-sm font-black transition-all hover:scale-105 active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus"></i> Request Subject
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-2xl border flex items-center gap-3 transition-all animate-slide-in <?php echo $messageType === 'success' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-rose-50 border-rose-100 text-rose-800'; ?>">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <p class="font-bold text-sm"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Stats Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-blue-50 text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-book"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Active</span>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Assigned Subjects</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $approved_count; ?> <span class="text-lg text-slate-300">/ 4</span></p>
                <div class="mt-6 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo ($approved_count/4)*100; ?>%"></div>
                </div>
            </div>

            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-orange-50 text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pipeline</span>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Pending Requests</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $pending_count; ?></p>
                <p class="text-xs font-bold text-orange-500 mt-4 uppercase tracking-widest">Awaiting Admin Review</p>
            </div>

            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Health</span>
                </div>
                <h3 class="text-sm font-bold text-slate-500">System Status</h3>
                <p class="text-4xl font-black text-slate-900 mt-2">Active</p>
                <p class="text-xs font-bold text-emerald-500 mt-4 uppercase tracking-widest">Operational • Verified</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <!-- Active Assignments -->
            <div>
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Teaching Load</h3>
                        <p class="text-slate-500 font-medium text-sm">Your approved subject assignments</p>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <?php if ($assignments->num_rows > 0): ?>
                        <?php while ($a = $assignments->fetch_assoc()): ?>
                            <div class="dashboard-card !p-6 group hover:border-blue-200 transition-all">
                                <div class="flex justify-between items-center text-left">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:bg-blue-600 group-hover:text-white transition-all duration-500">
                                            <i class="fas fa-book-reader"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-black text-slate-900"><?php echo htmlspecialchars($a['subject_name']); ?></h4>
                                            <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.15em] mt-1"><?php echo htmlspecialchars($a['subject_code']); ?> &bull; <span class="text-blue-600"><?php echo htmlspecialchars($a['class_level']); ?></span></p>
                                        </div>
                                    </div>
                                    <a href="enter_result.php?assignment_id=<?php echo $a['id']; ?>" class="bg-slate-50 text-slate-900 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-200 transition-all">
                                        Enter Results
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="dashboard-card !bg-transparent border-dashed border-2 border-slate-200 text-center py-12">
                            <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">No active assignments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pending Requests -->
            <div>
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Request Radar</h3>
                        <p class="text-slate-500 font-medium text-sm">Awaiting administrative validation</p>
                    </div>
                </div>
                
                <div class="dashboard-card !p-0 overflow-hidden">
                    <div class="p-8 pb-0">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Awaiting Approval</h4>
                    </div>
                    <?php if ($pending_requests->num_rows > 0): ?>
                        <div class="divide-y divide-slate-50">
                            <?php while ($p = $pending_requests->fetch_assoc()): ?>
                                <div class="px-8 py-6 hover:bg-slate-50 transition-all flex justify-between items-center">
                                    <div>
                                        <h5 class="font-bold text-slate-900"><?php echo htmlspecialchars($p['subject_name']); ?></h5>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1"><?php echo htmlspecialchars($p['class_level']); ?> &bull; <?php echo date('M d', strtotime($p['created_at'])); ?></p>
                                    </div>
                                    <span class="status-badge-premium bg-orange-50 text-orange-600 !text-[10px]">Pending</span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-12 text-center">
                            <i class="fas fa-check-circle text-slate-100 text-5xl mb-4"></i>
                            <p class="text-slate-400 font-medium text-sm italic">Queue is clear</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="hidden fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="document.getElementById('requestModal').classList.add('hidden')"></div>
    
    <div class="relative mx-auto max-w-lg bg-white rounded-[2rem] shadow-2xl overflow-hidden transform transition-all animate-modal-in">
        <div class="premium-gradient-bg p-8 text-white relative">
            <h3 class="text-2xl font-black tracking-tight">Request Subject</h3>
            <p class="text-blue-100 font-medium text-sm mt-1">Expanding your academic horizons</p>
            <button onclick="document.getElementById('requestModal').classList.add('hidden')" class="absolute top-8 right-8 text-white/50 hover:text-white transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form method="post" class="p-8">
            <input type="hidden" name="action" value="request_subject" />
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Academic Subject</label>
                    <select name="subject_id" required class="w-full bg-slate-50 border-slate-100 p-4 rounded-2xl font-bold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none">
                        <option value="">Select a subject to teach...</option>
                        <?php while ($s = $all_subjects->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name'] . ' (' . $s['subject_code'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                     <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Target Class Level</label>
                     <select name="class_level" required class="w-full bg-slate-50 border-slate-100 p-4 rounded-2xl font-bold text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none appearance-none">
                         <option value="Grade 5">Grade 5</option>
                         <option value="Grade 6">Grade 6</option>
                         <option value="Grade 7">Grade 7</option>
                         <option value="Grade 8">Grade 8</option>
                         <option value="Form 1">Form 1</option>
                         <option value="Form 2">Form 2</option>
                         <option value="Form 3">Form 3</option>
                         <option value="Form 4">Form 4</option>
                     </select>
                </div>
            </div>
            
            <div class="mt-10 flex gap-4">
                <button type="button" onclick="document.getElementById('requestModal').classList.add('hidden')" class="flex-1 px-8 py-4 bg-slate-100 text-slate-900 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-slate-200 transition-all">Cancel</button>
                <button type="submit" class="flex-2 px-8 py-4 premium-gradient-bg text-white rounded-2xl font-black uppercase tracking-widest text-xs hover:shadow-lg hover:shadow-blue-200 transition-all">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
