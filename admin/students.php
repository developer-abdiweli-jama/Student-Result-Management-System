<?php
// admin/students.php
require_once '../includes/middleware/admin_auth.php';
require_once '../config/database.php';

$action = $_GET['action'] ?? 'list';
$conn = getDBConnection();

// Handle different actions
switch ($action) {
    case 'add':
        handleAddStudent($conn);
        break;
    case 'edit':
        handleEditStudent($conn);
        break;
    case 'delete':
        handleDeleteStudent($conn);
        break;
    case 'view':
        handleViewStudent($conn);
        break;
    default:
        handleListStudents($conn);
        break;
}

$conn->close();

function handleAddStudent($conn) {
    $message = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitizeInput($_POST['name']);
        $class_level = $_POST['class_level'];
        $year_of_study = 0; // Legacy support or derived from class level
        // Derive numeric year for reg_no generation if needed (e.g. Form 1 -> 1)
        if (strpos($class_level, 'Form') !== false) {
             $year_of_study = (int)filter_var($class_level, FILTER_SANITIZE_NUMBER_INT);
        } elseif (strpos($class_level, 'Grade') !== false) {
             // Grades 5-8. Map to something? Or just keep 0.
             // Maybe reg_no doesn't strictly need year suffix anymore, or just use current year.
             $year_of_study = (int)filter_var($class_level, FILTER_SANITIZE_NUMBER_INT);
        }

        $admission_year = $_POST['admission_year'];
        $password = $_POST['password'];
        
        // Generate registration number
        $reg_no = generateRegNo($year_of_study);
        
        // Validate input
        if (validatePassword($password) && !empty($class_level)) {
            $password_hash = hashPassword($password);
            
            $stmt = $conn->prepare("INSERT INTO students (reg_no, name, password_hash, year_of_study, admission_year, class_level) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $reg_no, $name, $password_hash, $year_of_study, $admission_year, $class_level);
            
            if ($stmt->execute()) {
                $message = "Student added successfully! Registration Number: $reg_no";
            } else {
                $message = "Error adding student: " . $conn->error;
            }
            $stmt->close();
        } else {
            $message = "Invalid input data";
        }
    }
    
    displayStudentForm('add', null, $message);
}

function handleEditStudent($conn) {
    $id = (int)$_GET['id'];
    $message = '';
    
    // Get student data
    $stmt = $conn->prepare("SELECT id, reg_no, name, year_of_study, class_level, admission_year, avatar FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        header('Location: students.php?error=student_not_found');
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = sanitizeInput($_POST['name']);
        $class_level = $_POST['class_level'];
        $admission_year = $_POST['admission_year'];
        $password = $_POST['password'];
        
        if (!empty($class_level)) {
            // Only prepare and execute an update when inputs validate.
            $stmt = null;

            if (!empty($password)) {
                if (!validatePassword($password)) {
                    $message = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long";
                } else {
                    $password_hash = hashPassword($password);
                    $stmt = $conn->prepare("UPDATE students SET name = ?, class_level = ?, admission_year = ?, password_hash = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $name, $class_level, $admission_year, $password_hash, $id);
                }
            } else {
                $stmt = $conn->prepare("UPDATE students SET name = ?, class_level = ?, admission_year = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $class_level, $admission_year, $id);
            }

            // Execute only if a prepared statement was created (i.e. validation passed)
            if ($stmt) {
                if ($stmt->execute()) {
                    $message = "Student updated successfully!";
                } else {
                    $message = "Error updating student: " . $conn->error;
                }
                $stmt->close();
            }
        } else {
            $message = "Invalid year of study";
        }
    }
    
    displayStudentForm('edit', $student, $message);
}

function handleDeleteStudent($conn) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header('Location: students.php?message=student_deleted');
    } else {
        header('Location: students.php?error=delete_failed');
    }
    $stmt->close();
    exit();
}

function handleViewStudent($conn) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        header('Location: students.php?error=student_not_found');
        exit();
    }
    
    // Get student results
    $results = $conn->query("
        SELECT r.*, sub.subject_code, sub.subject_name, sub.credits
        FROM results r
        JOIN subjects sub ON r.subject_id = sub.id
        WHERE r.student_id = $id
        ORDER BY r.academic_year DESC, r.term, sub.subject_code
    ");
    
    displayStudentView($student, $results);
}

function handleListStudents($conn) {
    $search = $_GET['search'] ?? '';
    $year_filter = $_GET['year'] ?? '';
    
    $query = "SELECT * FROM students WHERE 1=1"; // note: will include avatar column
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR reg_no LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }
    
    if (!empty($year_filter)) {
        $query .= " AND class_level = ?";
        $params[] = $year_filter;
        $types .= "s";
    }
    
    $query .= " ORDER BY 
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
        created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $students = $stmt->get_result();
    
    displayStudentList($students, $search, $year_filter);
}

function displayStudentForm($action, $student = null, $message = '') {
    $page_title = $action === 'add' ? 'Add Student' : 'Edit Student';
    $page_scripts = ['admin/students.js', 'admin/avatars.js'];
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo $action === 'add' ? 'Add New Student' : 'Edit Student'; ?>
                </h1>
                <p class="text-gray-600">
                    <?php echo $action === 'add' ? 'Create a new student account' : 'Update student information'; ?>
                </p>
            </div>
            <a href="students.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                Back to List
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="mb-4 p-4 rounded-md <?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo $student['name'] ?? ''; ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="class_level" class="block text-sm font-medium text-gray-700">Class Level *</label>
                        <select id="class_level" name="class_level" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Class</option>
                            <optgroup label="Primary School">
                                <?php for ($i = 5; $i <= 8; $i++): ?>
                                <option value="Grade <?php echo $i; ?>" <?php echo (isset($student['class_level']) && $student['class_level'] == "Grade $i") ? 'selected' : ''; ?>>
                                    Grade <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </optgroup>
                            <optgroup label="Secondary School">
                                <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="Form <?php echo $i; ?>" <?php echo (isset($student['class_level']) && $student['class_level'] == "Form $i") ? 'selected' : ''; ?>>
                                    Form <?php echo $i; ?>
                                </option>
                                <?php endfor; ?>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div>
                        <label for="admission_year" class="block text-sm font-medium text-gray-700">Admission Year *</label>
                        <input type="text" id="admission_year" name="admission_year" required
                               value="<?php echo $student['admission_year'] ?? '2024/2025'; ?>"
                               placeholder="e.g. 2024/2025"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        <?php echo $action === 'add' ? 'Password *' : 'Password (leave blank to keep current)'; ?>
                    </label>
                    <input type="password" id="password" name="password" 
                           <?php echo $action === 'add' ? 'required' : ''; ?>
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="<?php echo $action === 'add' ? 'Enter password' : 'Enter new password'; ?>">
                    <p class="mt-1 text-sm text-gray-500">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</p>
                </div>
                
                <?php if ($action === 'edit' && isset($student['reg_no'])): ?>
                <div class="bg-gray-50 p-4 rounded-md">
                    <label class="block text-sm font-medium text-gray-700">Registration Number</label>
                    <p class="text-lg font-semibold text-blue-600"><?php echo $student['reg_no']; ?></p>
                    <p class="text-sm text-gray-500">This cannot be changed</p>
                </div>
                <?php endif; ?>
                
                <div class="flex justify-end space-x-3">
                    <a href="students.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200">
                        <?php echo $action === 'add' ? 'Add Student' : 'Update Student'; ?>
                    </button>
                </div>
            </form>
            <?php if ($action === 'edit' && isset($student['reg_no'])): ?>
            <div class="bg-white p-4 rounded-md mt-4">
                <label class="block text-sm font-medium text-gray-700">Student Avatar</label>
                <form action="../includes/upload_avatar.php" method="post" enctype="multipart/form-data" class="mt-2">
                    <input type="hidden" name="target_role" value="student" />
                    <input type="hidden" name="target_id" value="<?php echo $student['id']; ?>" />
                    <input type="file" name="avatar" accept="image/*" />
                    <button type="submit" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md">Upload Avatar</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayStudentList($students, $search = '', $year_filter = '') {
    $page_title = "Manage Students";
    $page_scripts = ['admin/students.js', 'admin/avatars.js'];
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Student Management</h1>
                <p class="text-gray-600">Manage student accounts and information</p>
            </div>
            <a href="students.php?action=add" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200">
                Add New Student
            </a>
        </div>
        
        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="action" value="list">
                
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search by name or reg no..."
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Filter by Class Level</label>
                    <select id="year" name="year" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Classes</option>
                        <option value="Form 4" <?php echo $year_filter == 'Form 4' ? 'selected' : ''; ?>>Form 4</option>
                        <option value="Form 3" <?php echo $year_filter == 'Form 3' ? 'selected' : ''; ?>>Form 3</option>
                        <option value="Form 2" <?php echo $year_filter == 'Form 2' ? 'selected' : ''; ?>>Form 2</option>
                        <option value="Form 1" <?php echo $year_filter == 'Form 1' ? 'selected' : ''; ?>>Form 1</option>
                        <option value="Grade 8" <?php echo $year_filter == 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
                        <option value="Grade 7" <?php echo $year_filter == 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
                        <option value="Grade 6" <?php echo $year_filter == 'Grade 6' ? 'selected' : ''; ?>>Grade 6</option>
                        <option value="Grade 5" <?php echo $year_filter == 'Grade 5' ? 'selected' : ''; ?>>Grade 5</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md transition duration-200 w-full">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Students Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($student = $students->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if (!empty($student['avatar'])): ?>
                                        <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>" alt="avatar" class="w-10 h-10 rounded-full object-cover mr-3" />
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm text-gray-700 mr-3"><?php echo htmlspecialchars(substr($student['name'], 0, 1)); ?></div>
                                    <?php endif; ?>
                                    <div class="text-sm font-medium text-blue-600"><?php echo $student['reg_no']; ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $student['name']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($student['class_level'] ?? 'Year ' . $student['year_of_study']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="students.php?action=view&id=<?php echo $student['id']; ?>" 
                                       class="text-green-600 hover:text-green-900 transition duration-200">View</a>
                                    <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 transition duration-200">Edit</a>
                                    <a href="students.php?action=delete&id=<?php echo $student['id']; ?>" 
                                       class="text-red-600 hover:text-red-900 transition duration-200"
                                       onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.')">Delete</a>
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

function displayStudentView($student, $results) {
    $page_title = "Student Details";
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 p-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Student Details</h1>
                <p class="text-gray-600">View student information and academic records</p>
            </div>
            <a href="students.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                Back to List
            </a>
        </div>
        
        <!-- Student Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Registration Number</label>
                        <p class="text-lg font-semibold text-blue-600"><?php echo $student['reg_no']; ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-lg text-gray-900"><?php echo $student['name']; ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Class Level</label>
                        <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student['class_level'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Admission Year</label>
                        <p class="text-lg text-gray-900"><?php echo htmlspecialchars($student['admission_year']); ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Account Created</label>
                        <p class="text-sm text-gray-600"><?php echo date('F j, Y, g:i A', strtotime($student['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" 
                       class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition duration-200 block text-center">
                        Edit Student Information
                    </a>
                    <a href="results.php?action=add&student_id=<?php echo $student['id']; ?>" 
                       class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200 block text-center">
                        Add New Result
                    </a>
                    <a href="../admin/export/result_pdf.php?student_id=<?php echo $student['id']; ?>" 
                       target="_blank"
                       class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition duration-200 block text-center">
                        Generate Result PDF
                    </a>
                    <a href="../admin/export/result_pdf.php?student_id=<?php echo $student['id']; ?>&pdf=1" 
                       class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition duration-200 block text-center">
                        Download PDF (server)
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Academic Results -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Academic Results</h3>
            </div>
            <div class="overflow-x-auto">
                <?php if ($results->num_rows > 0): ?>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade Point</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($result = $results->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                Semester <?php echo $result['semester']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $result['subject_name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $result['subject_code']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $result['marks_obtained']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $result['grade'] === 'F' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $result['grade']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $result['grade_point']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($result['exam_date'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500">This student hasn't been assigned any results yet.</p>
                    <div class="mt-6">
                        <a href="results.php?action=add&student_id=<?php echo $student['id']; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Add First Result
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}
?>