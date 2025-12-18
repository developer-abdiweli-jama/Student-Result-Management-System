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
<div class="lg:ml-64 flex-1 p-8 bg-gray-50 min-h-screen">
    <div class="mb-8">
        <p class="text-gray-600">Assign subjects to teachers (Max 4 per teacher)</p>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Manual Assignment Form -->
        <div class="lg:col-span-1">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 sticky top-4">
                <h3 class="text-lg font-semibold mb-4 text-gray-800">New Assignment</h3>
                <form method="post" class="space-y-4">
                    <input type="hidden" name="action" value="assign" />
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                        <select name="teacher_id" required class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select teacher</option>
                            <?php while ($t = $teachers->fetch_assoc()): ?>
                                <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name'] . ' (' . $t['reg_no'] . ')'); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                             <option value="">Select subject</option>
                            <?php while ($s = $subjects->fetch_assoc()): ?>
                                <option value="<?php echo $s['id']; ?>" data-class-level="<?php echo htmlspecialchars($s['class_level']); ?>">
                                    <?php echo htmlspecialchars($s['subject_name'] . ' (' . $s['subject_code'] . ') - ' . $s['class_level']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Class Level</label>
                        <select name="class_level" required class="w-full border border-gray-300 p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                             <option value="Form 1">Form 1</option>
                             <option value="Form 2">Form 2</option>
                             <option value="Form 3">Form 3</option>
                             <option value="Form 4">Form 4</option>
                             <option value="Grade 9">Grade 9</option>
                             <option value="Grade 10">Grade 10</option>
                             <option value="Grade 11">Grade 11</option>
                             <option value="Grade 12">Grade 12</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
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
            
            let foundMatch = false;
            options.forEach(option => {
                if (option.value === "") {
                    option.textContent = classLevel ? "Select subject for " + classLevel : "Select subject";
                    option.style.display = "";
                    return;
                }
                
                const optionClass = option.getAttribute('data-class-level');
                if (optionClass === classLevel) {
                    option.style.display = "";
                    if (!foundMatch) {
                        option.selected = false; // Reset selection so user must pick
                        foundMatch = true;
                    }
                } else {
                    option.style.display = "none";
                }
            });
            subjectSelect.value = ""; // Clear selection on class change
        });
        </script>

        <!-- Lists -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Pending Requests -->
            <?php if ($pending_assignments->num_rows > 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-orange-200 overflow-hidden">
                <div class="bg-orange-50 px-6 py-4 border-b border-orange-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-orange-800">Pending Requests</h3>
                    <span class="bg-orange-200 text-orange-800 text-xs px-2 py-1 rounded-full"><?php echo $pending_assignments->num_rows; ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($p = $pending_assignments->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['subject_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($p['class_level']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form method="post" class="inline-flex space-x-2">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>" />
                                            <button type="submit" name="action" value="approve" class="text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded border border-green-200">Approve</button>
                                            <button type="submit" name="action" value="reject" class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded border border-red-200">Reject</button>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Active Assignments</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($assignments->num_rows === 0): ?>
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No active assignments found.</td></tr>
                            <?php else: ?>
                                <?php while ($a = $assignments->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($a['name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($a['subject_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($a['class_level'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <form method="post" onsubmit="return confirm('Remove this assignment?');">
                                                <input type="hidden" name="action" value="delete" />
                                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>" />
                                                <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
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
