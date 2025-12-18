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

<div class="lg:ml-64 flex-1 p-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
        </div>
        
        <?php if ($approved_count < 4): ?>
        <button onclick="document.getElementById('requestModal').classList.remove('hidden')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md transition duration-200">
            Request Subject
        </button>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Active Subjects</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $approved_count; ?>/4</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-orange-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase">Pending Requests</h3>
            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $pending_count; ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Active Assignments -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">My Active Subjects</h3>
            </div>
            <div class="p-6">
                <?php if ($assignments->num_rows > 0): ?>
                    <div class="grid gap-4">
                        <?php while ($a = $assignments->fetch_assoc()): ?>
                            <div class="border rounded-lg p-4 hover:shadow-md transition duration-200">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-bold text-gray-900"><?php echo htmlspecialchars($a['subject_name']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($a['subject_code']); ?> &bull; <?php echo htmlspecialchars($a['class_level']); ?></p>
                                    </div>
                                    <a href="enter_result.php?assignment_id=<?php echo $a['id']; ?>" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium hover:bg-green-200">
                                        Enter Results
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic">No approved subjects yet.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Requests</h3>
            </div>
            <div class="p-6">
                <?php if ($pending_requests->num_rows > 0): ?>
                    <ul class="divide-y divide-gray-100">
                        <?php while ($p = $pending_requests->fetch_assoc()): ?>
                            <li class="py-3">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-900"><?php echo htmlspecialchars($p['subject_name']); ?></span>
                                    <span class="text-sm text-orange-600 bg-orange-50 px-2 py-1 rounded">Pending</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($p['class_level']); ?> &bull; Requested on <?php echo date('M d, Y', strtotime($p['created_at'])); ?></p>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-500 italic">No pending requests.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<div id="requestModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Request New Subject</h3>
        <form method="post">
            <input type="hidden" name="action" value="request_subject" />
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <select name="subject_id" required class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select subject</option>
                        <?php while ($s = $all_subjects->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name'] . ' (' . $s['subject_code'] . ')'); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-1">Class Level</label>
                     <select name="class_level" required class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 focus:border-blue-500">
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
            
            <div class="items-center px-4 py-3 mt-4 text-right">
                <button type="button" onclick="document.getElementById('requestModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
