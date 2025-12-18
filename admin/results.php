<?php
// admin/results.php
require_once '../includes/middleware/admin_auth.php';
require_once '../config/database.php';
require_once '../includes/settings.php';
require_once '../includes/validation.php';

$action = $_GET['action'] ?? 'list';
$conn = getDBConnection();

// Handle different actions
switch ($action) {
    case 'add':
        handleAddResult($conn);
        break;
    case 'edit':
        handleEditResult($conn);
        break;
    case 'delete':
        handleDeleteResult($conn);
        break;
    case 'bulk':
        handleBulkResults($conn);
        break;
    default:
        handleListResults($conn);
        break;
}

$conn->close();

function handleAddResult($conn) {
    $message = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $student_id = (int)$_POST['student_id'];
        $subject_id = (int)$_POST['subject_id'];
        $marks_obtained = (float)$_POST['marks_obtained'];
        $term = (int)$_POST['term'];
        $exam_date = $_POST['exam_date'];
        
        // Validate input
        if (validateMarks($marks_obtained) && validateTerm($term)) {
            // Calculate grade and grade point
            $grade_info = calculateGrade($marks_obtained);
            
            $academic_year = getSetting('current_academic_year', '2024/2025');
            
            // Validate Student Admission Year
            $yearValidation = validateStudentResultYear($student_id, $academic_year, $conn);
            if ($yearValidation !== true) {
                // If validation failed, showing the error message
                $message = "Error: " . $yearValidation;
                // We stop here
            } else {
                // Admin-entered results: entered_by_teacher_id is NULL
                $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, entered_by_teacher_id, marks_obtained, grade, grade_point, exam_date, term, academic_year) VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?)");
                // types: i (student), i (subject), d (marks), s (grade), d (point), s (exam_date), i (term), s (year)
                $stmt->bind_param("iidsdsis", $student_id, $subject_id, $marks_obtained, $grade_info['grade'], $grade_info['point'], $exam_date, $term, $academic_year);
                
                if ($stmt->execute()) {
                    $message = "Result added successfully!";
                } else {
                    if ($conn->errno === 1062) { // Duplicate entry
                        $message = "Error: This student already has a result for this subject in the selected term.";
                    } else {
                        $message = "Error adding result: " . $conn->error;
                    }
                }
                $stmt->close();
            }
        } else {
            $message = "Invalid input data. Marks must be between 0-100 and term between 1-3.";
        }
    }
    
    // Get students and subjects for dropdowns (include class_level for filtering)
    // Sort students: Form 4 (top) to Grade 5 (bottom)
    $students = $conn->query("
        SELECT id, reg_no, name, class_level FROM students 
        ORDER BY 
            CASE class_level
                WHEN 'Form 4' THEN 1
                WHEN 'Form 3' THEN 2
                WHEN 'Form 2' THEN 3
                WHEN 'Form 1' THEN 4
                WHEN 'Grade 8' THEN 5
                WHEN 'Grade 7' THEN 6
                WHEN 'Grade 6' THEN 7
                WHEN 'Grade 5' THEN 8
                ELSE 9
            END,
            reg_no
    ");
   $subjects = $conn->query("SELECT id, subject_code, subject_name, class_level FROM subjects ORDER BY class_level, subject_code");
    
    displayResultForm('add', null, $message, $students, $subjects);
}

function handleEditResult($conn) {
    $id = (int)$_GET['id'];
    $message = '';
    
    // Get result data
    $stmt = $conn->prepare("SELECT r.*, s.reg_no, s.name as student_name, sub.subject_code, sub.subject_name 
                           FROM results r 
                           JOIN students s ON r.student_id = s.id 
                           JOIN subjects sub ON r.subject_id = sub.id 
                           WHERE r.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$result) {
        header('Location: results.php?error=result_not_found');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $marks_obtained = isset($_POST['marks_obtained']) ? (float)$_POST['marks_obtained'] : null;
        $exam_date = isset($_POST['exam_date']) ? sanitizeInput($_POST['exam_date']) : null;

        if (validateMarks($marks_obtained)) {
            // Calculate grade and grade point
            $grade_info = calculateGrade($marks_obtained);

            // Use separate statement variables to avoid overwriting and double-close
            $updateStmt = $conn->prepare("UPDATE results SET marks_obtained = ?, grade = ?, grade_point = ?, exam_date = ? WHERE id = ?");
            // types: d (double), s (string), d (double), s (string), i (int)
            $updateStmt->bind_param("dsdsi", $marks_obtained, $grade_info['grade'], $grade_info['point'], $exam_date, $id);

            if ($updateStmt->execute()) {
                $message = "Result updated successfully!";
                // Refresh result data using a separate select statement
                $selectStmt = $conn->prepare("SELECT r.*, s.reg_no, s.name as student_name, sub.subject_code, sub.subject_name 
                                       FROM results r 
                                       JOIN students s ON r.student_id = s.id 
                                       JOIN subjects sub ON r.subject_id = sub.id 
                                       WHERE r.id = ?");
                $selectStmt->bind_param("i", $id);
                $selectStmt->execute();
                $result = $selectStmt->get_result()->fetch_assoc();
                $selectStmt->close();
            } else {
                $message = "Error updating result: " . $conn->error;
            }
            $updateStmt->close();
        } else {
            $message = "Invalid marks. Must be between 0-100.";
        }
    }
    
    displayResultForm('edit', $result, $message);
}

function handleDeleteResult($conn) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Location: results.php?message=result_deleted');
    } else {
        header('Location: results.php?error=delete_failed');
    }
    $stmt->close();
    exit();
}

function handleBulkResults($conn) {
    $message = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = (int)$_POST['term'];
    $exam_date = $_POST['exam_date'];
    $subject_id = (int)$_POST['subject_id'];
    $attributed_teacher = isset($_POST['attributed_teacher']) && $_POST['attributed_teacher'] !== '' ? (int)$_POST['attributed_teacher'] : null;
    $results_data = $_POST['results'];
        
        $success_count = 0;
        $error_count = 0;
        
        foreach ($results_data as $student_id => $marks) {
            if (!empty($marks) && validateMarks($marks)) {
                $grade_info = calculateGrade((float)$marks);
                
                $academic_year = getSetting('current_academic_year', '2024/2025');

                // Validate Student Admission Year
                $yearValidation = validateStudentResultYear($student_id, $academic_year, $conn);
                if ($yearValidation !== true) {
                    $error_count++;
                    continue; // Skip this student
                }

                if ($attributed_teacher) {
                    $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, entered_by_teacher_id, marks_obtained, grade, grade_point, exam_date, term, academic_year) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE marks_obtained = ?, grade = ?, grade_point = ?, exam_date = ?");
                    // types: i,i,i,d,s,d,s,i,s,d,s,d,s
                    $stmt->bind_param("iiidsdsisdsds", $student_id, $subject_id, $attributed_teacher, $marks, $grade_info['grade'], $grade_info['point'], $exam_date, $term, $academic_year,
                                     $marks, $grade_info['grade'], $grade_info['point'], $exam_date);
                } else {
                    $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, entered_by_teacher_id, marks_obtained, grade, grade_point, exam_date, term, academic_year) 
                                           VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?) 
                                           ON DUPLICATE KEY UPDATE marks_obtained = ?, grade = ?, grade_point = ?, exam_date = ?");
                    // params: student_id(i), subject_id(i), marks(d), grade(s), point(d), exam_date(s), term(i), academic_year(s), marks(d), grade(s), point(d), exam_date(s)
                    $stmt->bind_param("iidsdsisdsds", $student_id, $subject_id, $marks, $grade_info['grade'], $grade_info['point'], $exam_date, $term, $academic_year,
                                     $marks, $grade_info['grade'], $grade_info['point'], $exam_date);
                }
                
                if ($stmt->execute()) {
                    $success_count++;
                } else {
                    $error_count++;
                }
                $stmt->close();
            }
        }
        
        $message = "Bulk upload completed: $success_count results processed successfully, $error_count errors.";
    }
    
    // Get students and subjects for bulk form (include class_level for filtering)
    // Sort students: Form 4 (top) to Grade 5 (bottom)
    $students = $conn->query("
        SELECT id, reg_no, name, class_level FROM students 
        ORDER BY 
            CASE class_level
                WHEN 'Form 4' THEN 1
                WHEN 'Form 3' THEN 2
                WHEN 'Form 2' THEN 3
                WHEN 'Form 1' THEN 4
                WHEN 'Grade 8' THEN 5
                WHEN 'Grade 7' THEN 6
                WHEN 'Grade 6' THEN 7
                WHEN 'Grade 5' THEN 8
                ELSE 9
            END,
            reg_no
    ");
    $subjects = $conn->query("SELECT id, subject_code, subject_name, class_level FROM subjects ORDER BY class_level, subject_code");
    
    displayBulkResultForm($message, $students, $subjects);
}

function handleListResults($conn) {
    $search = $_GET['search'] ?? '';
    $term_filter = $_GET['term'] ?? '';
    $subject_filter = $_GET['subject'] ?? '';
    $class_filter = $_GET['class_level'] ?? '';
    
    $query = "SELECT r.*, s.reg_no, s.name as student_name, sub.subject_code, sub.subject_name, t.id AS entered_by_teacher_id, t.name AS entered_by_teacher_name 
              FROM results r 
              JOIN students s ON r.student_id = s.id 
              JOIN subjects sub ON r.subject_id = sub.id 
              LEFT JOIN teachers t ON r.entered_by_teacher_id = t.id 
              WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $query .= " AND (s.name LIKE ? OR s.reg_no LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
    
    if (!empty($term_filter)) {
        $query .= " AND r.term = ?";
        $params[] = $term_filter;
        $types .= "i";
    }
    
    if (!empty($subject_filter)) {
        $query .= " AND r.subject_id = ?";
        $params[] = $subject_filter;
        $types .= "i";
    }

    if (!empty($class_filter)) {
        $query .= " AND sub.class_level = ?";
        $params[] = $class_filter;
        $types .= "s";
    }
    
    $query .= " ORDER BY r.term DESC, s.reg_no, sub.subject_code";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();
    
    // Get subjects for filter
    $subjects = $conn->query("SELECT id, subject_code, subject_name FROM subjects ORDER BY subject_code");
    
    displayResultList($results, $search, $term_filter, $subject_filter, $class_filter, $subjects);
}

function displayResultForm($action, $result = null, $message = '', $students = null, $subjects = null) {
    $page_title = $action === 'add' ? 'Add Result' : 'Edit Result';
    $page_scripts = ['admin/results.js'];
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo $action === 'add' ? 'Add New Result' : 'Edit Result'; ?>
                </h1>
                <p class="text-gray-600">
                    <?php echo $action === 'add' ? 'Enter student examination results' : 'Update result information'; ?>
                </p>
            </div>
            <a href="results.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                Back to List
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6" id="resultForm">
                <?php if ($action === 'add'): ?>
                <!-- Class Level Selector First -->
                <div class="mb-4">
                    <label for="class_level_filter" class="block text-sm font-medium text-gray-700">Class Level * (Select First)</label>
                    <select id="class_level_filter" name="class_level_filter" required
                            class="mt-1 block w-full md:w-1/2 px-3 py-2 border border-blue-500 bg-blue-50 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Class Level</option>
                        <option value="Form 4">Form 4</option>
                        <option value="Form 3">Form 3</option>
                        <option value="Form 2">Form 2</option>
                        <option value="Form 1">Form 1</option>
                        <option value="Grade 8">Grade 8</option>
                        <option value="Grade 7">Grade 7</option>
                        <option value="Grade 6">Grade 6</option>
                        <option value="Grade 5">Grade 5</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="student_id" class="block text-sm font-medium text-gray-700">Student *</label>
                        <select id="student_id" name="student_id" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Class Level First</option>
                            <?php while($student = $students->fetch_assoc()): ?>
                            <option value="<?php echo $student['id']; ?>" <?php echo (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) ? 'selected' : ''; ?> data-class-level="<?php echo htmlspecialchars($student['class_level'] ?? ''); ?>">
                                <?php echo $student['reg_no'] . ' - ' . $student['name'] . ' (' . htmlspecialchars($student['class_level'] ?? 'N/A') . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject *</label>
                        <select id="subject_id" name="subject_id" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Class Level First</option>
                            <?php while($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?php echo $subject['id']; ?>" data-class-level="<?php echo $subject['class_level']; ?>">
                                <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name'] . ' (' . $subject['class_level'] . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="term" class="block text-sm font-medium text-gray-700">Term *</label>
                        <select id="term" name="term" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Term</option>
                            <?php for ($i = 1; $i <= 2; $i++): ?>
                            <option value="<?php echo $i; ?>">Term <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="exam_date" class="block text-sm font-medium text-gray-700">Exam Date *</label>
                        <input type="date" id="exam_date" name="exam_date" required
                               value="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-gray-50 p-4 rounded-md space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Student</label>
                        <p class="text-lg font-semibold text-blue-600"><?php echo $result['reg_no'] . ' - ' . $result['student_name']; ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <p class="text-lg text-gray-900"><?php echo $result['subject_code'] . ' - ' . $result['subject_name']; ?></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Term</label>
                            <p class="text-lg text-gray-900">Term <?php echo $result['term']; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Grade</label>
                            <p class="text-lg font-semibold <?php echo $result['grade'] === 'F' ? 'text-red-600' : 'text-green-600'; ?>">
                                <?php echo $result['grade']; ?> (<?php echo $result['grade_point']; ?>)
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="marks_obtained" class="block text-sm font-medium text-gray-700">Marks Obtained *</label>
                        <input type="number" id="marks_obtained" name="marks_obtained" min="0" max="100" step="0.01" required
                               value="<?php echo $result['marks_obtained'] ?? ''; ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter marks (0-100)">
                        <p class="mt-1 text-sm text-gray-500">Marks must be between 0 and 100</p>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-md">
                        <label class="block text-sm font-medium text-gray-700">Grade Preview</label>
                        <div id="gradePreview" class="mt-2">
                            <span class="text-lg font-semibold text-gray-600">Enter marks to see grade</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="results.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                        <?php echo $action === 'add' ? 'Add Result' : 'Update Result'; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Grade Scale Reference -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Grade Scale Reference</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <?php foreach (GRADE_SCALE as $grade => $range): ?>
                <div class="text-center p-3 rounded-lg <?php echo $grade === 'F' ? 'bg-red-100' : 'bg-green-100'; ?>">
                    <div class="font-semibold <?php echo $grade === 'F' ? 'text-red-800' : 'text-green-800'; ?>"><?php echo $grade; ?></div>
                    <div class="text-sm <?php echo $grade === 'F' ? 'text-red-600' : 'text-green-600'; ?>">
                        <?php echo $range['min']; ?>-<?php echo $range['max']; ?>%
                    </div>
                    <div class="text-xs <?php echo $grade === 'F' ? 'text-red-600' : 'text-green-600'; ?>">
                        GP: <?php echo $range['point']; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayBulkResultForm($message = '', $students = null, $subjects = null) {
    $page_title = "Bulk Results Upload";
    $page_scripts = ['admin/results.js'];
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    $conn = getDBConnection();
    $teachers_for_select = $conn->query("SELECT id, reg_no, name FROM teachers ORDER BY name");
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bulk Results Upload</h1>
                <p class="text-gray-600">Upload results for multiple students at once</p>
            </div>
            <a href="results.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                Back to List
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="POST" class="space-y-6" id="bulkResultForm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Class Level Selector -->
                    <div>
                        <label for="bulk_class_level" class="block text-sm font-medium text-gray-700">Class Level *</label>
                        <select id="bulk_class_level" name="bulk_class_level" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Class Level</option>
                            <option value="Form 4">Form 4</option>
                            <option value="Form 3">Form 3</option>
                            <option value="Form 2">Form 2</option>
                            <option value="Form 1">Form 1</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="Grade 5">Grade 5</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="bulk_subject_id" class="block text-sm font-medium text-gray-700">Subject *</label>
                        <select id="bulk_subject_id" name="bulk_subject_id" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Class First</option>
                            <?php while($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?php echo $subject['id']; ?>"
                                    data-class-level="<?php echo htmlspecialchars($subject['class_level']); ?>">
                                <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name'] . ' (' . $subject['class_level'] . ')'; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="term" class="block text-sm font-medium text-gray-700">Term *</label>
                        <select id="term" name="term" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Term</option>
                            <?php for ($i = 1; $i <= 2; $i++): ?>
                            <option value="<?php echo $i; ?>">Term <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="exam_date" class="block text-sm font-medium text-gray-700">Exam Date *</label>
                        <input type="date" id="exam_date" name="exam_date" required
                               value="<?php echo date('Y-m-d'); ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="border-t border-gray-200 pt-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Attributed to Teacher (optional)</label>
                        <select name="attributed_teacher" class="mt-1 block w-1/2 px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">-- None --</option>
                            <?php while ($th = $teachers_for_select->fetch_assoc()): ?>
                                <option value="<?php echo $th['id']; ?>"><?php echo htmlspecialchars($th['reg_no'] . ' - ' . $th['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Marks</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks (0-100)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while($student = $students->fetch_assoc()): ?>
                                <tr data-class-level="<?php echo htmlspecialchars($student['class_level'] ?? ''); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo $student['reg_no']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $student['name'] . ' (' . htmlspecialchars($student['class_level'] ?? 'N/A') . ')'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number" 
                                               name="results[<?php echo $student['id']; ?>]" 
                                               min="0" max="100" step="0.01"
                                               class="w-24 px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Marks">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="results.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                        Upload Results
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayResultList($results, $search = '', $term_filter = '', $subject_filter = '', $class_filter = '', $subjects = null) {
    $page_title = "Manage Results";
    $page_scripts = ['admin/results.js'];
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Results Management</h1>
                <p class="text-gray-600">Manage student examination results</p>
            </div>
            <div class="flex space-x-3">
                <a href="results.php?action=bulk" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
                    Bulk Upload
                </a>
                <a href="results.php?action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                    Add New Result
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="hidden" name="action" value="list">
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by student name or reg no..."
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="term" class="block text-sm font-medium text-gray-700">Term</label>
                    <select id="term" name="term" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Terms</option>
                        <?php for ($i = 1; $i <= 2; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo $term_filter == $i ? 'selected' : ''; ?>>Term <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <select id="subject" name="subject" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Subjects</option>
                        <?php while($subject = $subjects->fetch_assoc()): ?>
                        <option value="<?php echo $subject['id']; ?>" <?php echo $subject_filter == $subject['id'] ? 'selected' : ''; ?>>
                            <?php echo $subject['subject_code']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label for="class_level" class="block text-sm font-medium text-gray-700">Class Level</label>
                    <select id="class_level" name="class_level" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Classes</option>
                        <?php 
                        $classes = ['Form 4', 'Form 3', 'Form 2', 'Form 1', 'Grade 8', 'Grade 7', 'Grade 6', 'Grade 5'];
                        foreach ($classes as $c): ?>
                            <option value="<?php echo $c; ?>" <?php echo $class_filter == $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200 w-full">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Results Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entered By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($result = $results->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $result['student_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $result['reg_no']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $result['subject_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $result['subject_code']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Term <?php echo $result['term']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo $result['marks_obtained']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $result['grade'] === 'F' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $result['grade']; ?> (<?php echo $result['grade_point']; ?>)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($result['exam_date'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo !empty($result['entered_by_teacher_name']) ? htmlspecialchars($result['entered_by_teacher_name']) : 'Admin'; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="results.php?action=edit&id=<?php echo $result['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition duration-200">Edit</a>
                                    <a href="results.php?action=delete&id=<?php echo $result['id']; ?>" 
                                       class="text-red-600 hover:text-red-900 transition duration-200"
                                       onclick="return confirm('Are you sure you want to delete this result? This action cannot be undone.')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}
?>