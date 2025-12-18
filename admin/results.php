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
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <!-- Glass Header -->
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                        <?php echo $action === 'add' ? 'Record Result' : 'Update Score'; ?>
                    </h1>
                    <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span>Academics</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold"><?php echo $action === 'add' ? 'New Entry' : 'Edit Result'; ?></span>
                    </nav>
                </div>
                <a href="results.php" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50 transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Results
                </a>
            </div>
        </div>
        
        <div class="max-w-4xl mx-auto px-8">
            <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 <?php echo strpos($message, 'Error') !== false ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100'; ?> animate-fade-in-up">
                <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <span class="font-bold text-sm"><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-card p-0 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Exam Details</h3>
                    <?php if ($action !== 'add'): ?>
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-bold">ID: #<?php echo $result['id']; ?></span>
                    <?php endif; ?>
                </div>

                <form method="POST" class="p-8 space-y-8" id="resultForm">
                    <?php if ($action === 'add'): ?>
                    <!-- Class Level Selector First -->
                    <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 mb-8">
                        <label for="class_level_filter" class="block text-xs font-black text-blue-600 uppercase tracking-widest mb-2 pl-1">Target Class Level</label>
                        <div class="relative">
                            <i class="fas fa-layer-group absolute left-4 top-1/2 -translate-y-1/2 text-blue-400"></i>
                            <select id="class_level_filter" name="class_level_filter" required
                                    class="w-full pl-11 pr-4 py-3 bg-white border-blue-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none cursor-pointer hover:border-blue-400">
                                <option value="">Select Level First...</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Form 3">Form 3</option>
                                <option value="Form 2">Form 2</option>
                                <option value="Form 1">Form 1</option>
                                <option value="Grade 8">Grade 8</option>
                                <option value="Grade 7">Grade 7</option>
                                <option value="Grade 6">Grade 6</option>
                                <option value="Grade 5">Grade 5</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-blue-400 pointer-events-none"></i>
                        </div>
                        <p class="text-xs text-blue-500 mt-2 font-medium pl-1"><i class="fas fa-info-circle mr-1"></i> Selecting a class will filter students and subjects.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="student_id" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Student Selection</label>
                            <div class="relative">
                                <i class="fas fa-user-graduate absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select id="student_id" name="student_id" required
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">Waiting for Class Level...</option>
                                    <?php while($student = $students->fetch_assoc()): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo (isset($_GET['student_id']) && $_GET['student_id'] == $student['id']) ? 'selected' : ''; ?> data-class-level="<?php echo htmlspecialchars($student['class_level'] ?? ''); ?>">
                                        <?php echo $student['reg_no'] . ' - ' . $student['name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="subject_id" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Subject Selection</label>
                            <div class="relative">
                                <i class="fas fa-book absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select id="subject_id" name="subject_id" required
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none disabled:bg-slate-100 disabled:text-slate-400">
                                    <option value="">Waiting for Class Level...</option>
                                    <?php while($subject = $subjects->fetch_assoc()): ?>
                                    <option value="<?php echo $subject['id']; ?>" data-class-level="<?php echo $subject['class_level']; ?>">
                                        <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="term" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Term</label>
                            <div class="relative">
                                <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select id="term" name="term" required
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all appearance-none">
                                    <option value="">Select Term</option>
                                    <?php for ($i = 1; $i <= 2; $i++): ?>
                                    <option value="<?php echo $i; ?>">Term <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>
                        
                        <div>
                            <label for="exam_date" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Exam Date</label>
                            <div class="relative">
                                <i class="fas fa-clock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="date" id="exam_date" name="exam_date" required
                                       value="<?php echo date('Y-m-d'); ?>"
                                       class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Edit Mode Read-Only Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Student</label>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                                    <?php echo substr($result['student_name'], 0, 1); ?>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-slate-900 leading-tight"><?php echo $result['student_name']; ?></p>
                                    <p class="text-xs font-medium text-slate-500"><?php echo $result['reg_no']; ?></p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Subject</label>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div>
                                    <p class="text-lg font-bold text-slate-900 leading-tight"><?php echo $result['subject_name']; ?></p>
                                    <p class="text-xs font-medium text-slate-500"><?php echo $result['subject_code']; ?> &bull; Term <?php echo $result['term']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="border-t border-slate-100 pt-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                                <label for="marks_obtained" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Score Obtained</label>
                                <div class="relative">
                                    <input type="number" id="marks_obtained" name="marks_obtained" min="0" max="100" step="0.01" required
                                           value="<?php echo $result['marks_obtained'] ?? ''; ?>"
                                           class="w-full text-4xl font-black text-center py-4 bg-white border-2 border-slate-200 rounded-2xl text-slate-900 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 outline-none transition-all placeholder:text-slate-200"
                                           placeholder="00">
                                    <span class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-lg">/ 100</span>
                                </div>
                                <p class="text-xs text-center text-slate-400 mt-2 font-medium">Enter value between 0 and 100</p>
                            </div>
                            
                            <div class="flex flex-col justify-center h-full">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Grade Preview</label>
                                <div id="gradePreview" class="flex-1 bg-slate-900 text-white rounded-2xl p-6 flex flex-col items-center justify-center relative overflow-hidden group">
                                     <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                     <div class="text-center relative z-10">
                                         <span class="text-5xl font-black tracking-tighter" id="previewGrade">--</span>
                                         <span class="block text-slate-400 text-sm font-bold mt-1" id="previewPoint">GP: --</span>
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 pt-4">
                        <a href="results.php" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-100 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="premium-btn px-8 py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20">
                            <i class="fas fa-check-circle mr-2"></i> <?php echo $action === 'add' ? 'Save Result' : 'Update Changes'; ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Grade Scale Reference -->
            <div class="mt-8">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4 pl-1">Marking Scheme Reference</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <?php foreach (GRADE_SCALE as $grade => $range): ?>
                    <div class="dashboard-card p-4 text-center hover:-translate-y-1 transition-transform cursor-default <?php echo $grade === 'F' ? 'border-l-4 border-l-rose-500' : 'border-l-4 border-l-emerald-500'; ?>">
                        <div class="text-2xl font-black <?php echo $grade === 'F' ? 'text-rose-600' : 'text-slate-800'; ?>"><?php echo $grade; ?></div>
                        <div class="text-xs font-bold text-slate-400 mt-1">
                            <?php echo $range['min']; ?> - <?php echo $range['max']; ?>%
                        </div>
                        <div class="text-[10px] font-bold text-slate-300 mt-1 uppercase tracking-wider">
                            Points: <?php echo $range['point']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayBulkResultForm($message = '', $students = null, $subjects = null) {
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    $conn = getDBConnection();
    $teachers_for_select = $conn->query("SELECT id, reg_no, name FROM teachers ORDER BY name");
    ?>
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Bulk Results Upload</h1>
                        <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span>Academics</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold">Bulk Entry</span>
                    </nav>
                </div>
                <a href="results.php" class="px-4 py-2 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl text-xs hover:bg-slate-50 transition-colors shadow-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Results
                </a>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-8">
            <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl flex items-center gap-3 <?php echo strpos($message, 'Error') !== false ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100'; ?> animate-fade-in-up">
                <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <span class="font-bold text-sm"><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" id="bulkResultForm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Control Panel -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="dashboard-card sticky top-32">
                            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                                <i class="fas fa-sliders-h text-blue-500"></i> Configuration
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="bulk_class_level" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">1. Class Level</label>
                                    <select id="bulk_class_level" name="bulk_class_level" required
                                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                        <option value="">Select Level...</option>
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
                                    <label for="bulk_subject_id" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">2. Subject</label>
                                    <select id="bulk_subject_id" name="bulk_subject_id" required
                                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all disabled:bg-slate-100 disabled:text-slate-400">
                                        <option value="">Select Class First...</option>
                                        <?php while($subject = $subjects->fetch_assoc()): ?>
                                        <option value="<?php echo $subject['id']; ?>"
                                                data-class-level="<?php echo htmlspecialchars($subject['class_level']); ?>">
                                            <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="term" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">3. Term</label>
                                        <select id="term" name="term" required
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                            <option value="">Term...</option>
                                            <option value="1">Term 1</option>
                                            <option value="2">Term 2</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="exam_date" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">4. Date</label>
                                        <input type="date" id="exam_date" name="exam_date" required
                                               value="<?php echo date('Y-m-d'); ?>"
                                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                                
                                <div class="pt-4 border-t border-slate-100">
                                     <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Attributed Teacher (Optional)</label>
                                     <select name="attributed_teacher" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                                        <option value="">-- No specific teacher --</option>
                                        <?php while ($th = $teachers_for_select->fetch_assoc()): ?>
                                            <option value="<?php echo $th['id']; ?>"><?php echo htmlspecialchars($th['reg_no'] . ' - ' . $th['name']); ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="pt-6">
                                    <button type="submit" class="premium-btn w-full py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
                                        <i class="fas fa-cloud-upload-alt"></i> Upload Results
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Students List -->
                    <div class="lg:col-span-2">
                        <div class="dashboard-card p-0 overflow-hidden min-h-[500px]">
                            <div class="px-6 py-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800">Student Entry List</h3>
                                <span class="text-xs font-medium text-slate-500 bg-white px-2 py-1 rounded border border-slate-200">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i> Showing students for selected class
                                </span>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Student Info</th>
                                            <th class="px-6 py-4 text-center text-xs font-black text-slate-400 uppercase tracking-wider w-40">Score (0-100)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <?php while($student = $students->fetch_assoc()): ?>
                                        <tr data-class-level="<?php echo htmlspecialchars($student['class_level'] ?? ''); ?>" class="hover:bg-slate-50/50 transition-colors hidden">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs ring-2 ring-white">
                                                        <?php echo substr($student['name'], 0, 1); ?>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-bold text-slate-900"><?php echo $student['name']; ?></div>
                                                        <div class="text-xs font-medium text-slate-500"><?php echo $student['reg_no']; ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 bg-slate-50/30">
                                                <input type="number" 
                                                       name="results[<?php echo $student['id']; ?>]" 
                                                       min="0" max="100" step="0.01"
                                                       class="w-full text-center px-3 py-2 bg-white border border-slate-200 rounded-lg text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-slate-300"
                                                       placeholder="-">
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <tr id="no-students-placeholder">
                                            <td colspan="2" class="px-6 py-12 text-center text-slate-400">
                                                <i class="fas fa-users text-4xl mb-3 opacity-20"></i>
                                                <p class="font-medium">Select a Class Level to load students</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayResultList($results, $search = '', $term_filter = '', $subject_filter = '', $class_filter = '', $subjects = null) {
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <!-- Glass Header -->
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Results Archive</h1>
                    <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold">Results</span>
                    </nav>
                </div>
                <div class="flex space-x-3">
                    <a href="results.php?action=bulk" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl text-sm hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                        <i class="fas fa-file-upload text-slate-400"></i> Bulk Upload
                    </a>
                    <a href="results.php?action=add" class="premium-btn px-5 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 flex items-center gap-2">
                        <i class="fas fa-plus"></i> New Entry
                    </a>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-8">
            <!-- Search & Filter Card -->
            <div class="dashboard-card mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    <input type="hidden" name="action" value="list">
                    
                    <div class="md:col-span-4">
                        <label for="search" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Search Records</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Student name, Reg No..."
                                   class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="class_level" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Class</label>
                        <select id="class_level" name="class_level" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Classes</option>
                            <?php 
                            $classes = ['Form 4', 'Form 3', 'Form 2', 'Form 1', 'Grade 8', 'Grade 7', 'Grade 6', 'Grade 5'];
                            foreach ($classes as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $class_filter == $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label for="subject" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Subject</label>
                        <select id="subject" name="subject" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Subjects</option>
                            <?php while($subject = $subjects->fetch_assoc()): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo $subject_filter == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo $subject['subject_code']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="term" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Term</label>
                        <select id="term" name="term" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Terms</option>
                            <?php for ($i = 1; $i <= 2; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $term_filter == $i ? 'selected' : ''; ?>>Term <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="md:col-span-1">
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm flex items-center justify-center">
                            <i class="fas fa-filter text-lg"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Results Table -->
            <div class="dashboard-card p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-modern w-full">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Subject & Term</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Score</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Grade</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Exam Date</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Input By</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($result = $results->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs ring-2 ring-white shadow-sm">
                                            <?php echo substr($result['student_name'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-900"><?php echo $result['student_name']; ?></div>
                                            <div class="text-xs font-medium text-slate-400"><?php echo $result['reg_no']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900"><?php echo $result['subject_name']; ?></div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        Term <?php echo $result['term']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-700">
                                    <?php echo $result['marks_obtained']; ?><span class="text-slate-400 font-medium text-xs">/100</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-black
                                        <?php echo $result['grade'] === 'F' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100'; ?>">
                                        <?php echo $result['grade']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-500">
                                    <?php echo date('M j, Y', strtotime($result['exam_date'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($result['entered_by_teacher_name'])): ?>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-indigo-400"></div>
                                            <span class="text-xs font-medium text-slate-600"><?php echo htmlspecialchars($result['entered_by_teacher_name']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <span class="text-xs font-medium text-slate-600">Admin</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="results.php?action=edit&id=<?php echo $result['id']; ?>" 
                                           class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                           title="Edit Result">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="results.php?action=delete&id=<?php echo $result['id']; ?>" 
                                           class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                           onclick="return confirm('Are you sure you want to delete this result? This action cannot be undone.')"
                                           title="Delete Result">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($results->num_rows == 0): ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fas fa-clipboard-list text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">No results found</h3>
                <p class="text-slate-500 text-sm mt-1">Try adjusting your filters or search terms.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}
?>