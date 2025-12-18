<?php
require_once __DIR__ . '/../includes/middleware/teacher_auth.php';
require_once __DIR__ . '/../config/database.php';

requireTeacher();
$conn = getDBConnection();
$teacher_id = $_SESSION['user_id'];

$assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

// Verify teacher assignment
$stmt = $conn->prepare('SELECT id, subject_id, class_level FROM teacher_assignments WHERE teacher_id = ? AND id = ? AND status = "approved"');
$stmt->bind_param('ii', $teacher_id, $assignment_id);
$stmt->execute();
$assign = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assign) {
    die('Assignment not found or not approved.');
}

$subject_id = $assign['subject_id'];

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $marks = (float)$_POST['marks_obtained'];
    $exam_date = $_POST['exam_date'];
    
    // We don't have semester/year stored in assignments anymore, we use class_level.
    // However, results table uses `semester`. 
    // We need to decide what `semester` means for High School. Term? 
    // User goal: "remove university concepts like semesters".
    // But DB still has `term` or `semester`? 
    // Earlier migration replaced `semester` logic.
    // Let's assume we use a "Term" input or default to 1 if user didn't specify.
    // Actually, user modified `results` table to use `term`? 
    // Let's check the migration summary. "Modified the results table to use term instead of semester."
    // So I should check if `term` column exists or if `semester` was renamed.
    
    // Wait, the Summary said: "Modified the results table to use term instead of semester." 
    // But I viewed `database.sql` earlier and it had `semester` in `results`. 
    // Let's assume the migration script `migration_v2_highschool.sql` handled it.
    // If I look at `admin/results.php` it handled `semester`.
    // I will use `semester` column but label it "Term" in UI.
    
    $term = (int)$_POST['term'];

    require_once __DIR__ . '/../includes/functions.php';
    // Ensure calculateGrade exists
    if (!function_exists('calculateGrade')) {
         function calculateGrade($marks) {
            if ($marks >= 80) return ['grade' => 'A', 'point' => 4.0];
            if ($marks >= 70) return ['grade' => 'B', 'point' => 3.0];
            if ($marks >= 60) return ['grade' => 'C', 'point' => 2.0];
            if ($marks >= 50) return ['grade' => 'D', 'point' => 1.0];
            return ['grade' => 'F', 'point' => 0.0];
        }
    }
    
    $grade_info = calculateGrade((float)$marks);
    $grade = $grade_info['grade'];
    $gp = $grade_info['point'];

    require_once __DIR__ . '/../includes/settings.php';
    require_once __DIR__ . '/../includes/validation.php'; // Ensure validation utils are loaded
    $academic_year = getSetting('current_academic_year', '2024/2025');

    // Validate Student Admission Year
    $yearValidation = validateStudentResultYear($student_id, $academic_year, $conn);
    if ($yearValidation !== true) {
        $message = 'Error: ' . $yearValidation;
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare('INSERT INTO results (student_id, subject_id, entered_by_teacher_id, marks_obtained, grade, grade_point, exam_date, term, academic_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE marks_obtained=?, grade=?, grade_point=?, exam_date=?, term=?, academic_year=?');
        $stmt->bind_param('iiidsssisdsssis', $student_id, $subject_id, $teacher_id, $marks, $grade, $gp, $exam_date, $term, $academic_year, $marks, $grade, $gp, $exam_date, $term, $academic_year);
        
        if ($stmt->execute()) {
            $message = 'Result recorded successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Get Students. Filter by the class level of this assignment.
$class_level = $assign['class_level'];
$stmtS = $conn->prepare('SELECT id, reg_no, name FROM students WHERE class_level = ? ORDER BY reg_no');
$stmtS->bind_param('s', $class_level);
$stmtS->execute();
$students = $stmtS->get_result();
$stmtS->close();

$page_title = 'Enter Result';
include __DIR__ . '/../includes/header.php';
?>

<?php include '../includes/teacher_sidebar.php'; ?>

<div class="lg:ml-64 flex-1 p-8 bg-gray-50 min-h-screen">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Enter Results</h1>
        <p class="text-gray-600">For Class: <?php echo htmlspecialchars($assign['class_level'] ?? 'N/A'); ?></p>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-sm p-6 max-w-2xl">
        <form method="post" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Student</label>
                    <select name="student_id" required class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Student</option>
                        <?php while ($s = $students->fetch_assoc()): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['reg_no'] . ' - ' . $s['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
                    <select name="term" required class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Exam Date</label>
                    <input name="exam_date" type="date" required class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" value="<?php echo date('Y-m-d'); ?>" />
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marks Obtained (0-100)</label>
                    <input name="marks_obtained" type="number" step="0.01" min="0" max="100" required class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. 85.5" />
                </div>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
                Save Result
            </button>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
