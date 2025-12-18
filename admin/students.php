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
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <!-- Glass Header -->
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                        <?php echo $action === 'add' ? 'New Registration' : 'Update Profile'; ?>
                    </h1>
                    <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <a href="students.php" class="hover:text-blue-600 transition-colors">Students</a>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold"><?php echo $action === 'add' ? 'Register' : 'Edit'; ?></span>
                    </nav>
                </div>
                <a href="students.php" class="px-4 py-2 bg-white text-slate-600 rounded-xl font-bold text-xs border border-slate-200 hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Directory</span>
                </a>
            </div>
        </div>
        
        <div class="max-w-4xl mx-auto px-8">
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-xl flex items-center gap-3 <?php echo strpos($message, 'Error') !== false ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100'; ?> animate-fade-in-up">
                <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <span class="font-bold text-sm"><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
                <div class="p-8 border-b border-slate-50 flex items-center gap-4 bg-slate-50/30">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <i class="fas <?php echo $action === 'add' ? 'fa-user-plus' : 'fa-user-edit'; ?> text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Student Information</h2>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                            <?php echo $action === 'add' ? 'Create a new student account' : 'Update existing records'; ?>
                        </p>
                    </div>
                </div>
                
                <div class="p-8">
                    <form method="POST" class="space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="name" class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Full Name</label>
                                <input type="text" id="name" name="name" required
                                       value="<?php echo $student['name'] ?? ''; ?>"
                                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                                       placeholder="e.g. John Doe">
                            </div>
                            
                            <div class="space-y-2">
                                <label for="class_level" class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Class Level</label>
                                <div class="relative">
                                    <select id="class_level" name="class_level" required
                                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all appearance-none cursor-pointer">
                                        <option value="">Select Academic Level</option>
                                        <optgroup label="Primary School" class="font-bold text-slate-900">
                                            <?php for ($i = 5; $i <= 8; $i++): ?>
                                            <option value="Grade <?php echo $i; ?>" <?php echo (isset($student['class_level']) && $student['class_level'] == "Grade $i") ? 'selected' : ''; ?>>
                                                Grade <?php echo $i; ?>
                                            </option>
                                            <?php endfor; ?>
                                        </optgroup>
                                        <optgroup label="Secondary School" class="font-bold text-slate-900">
                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                            <option value="Form <?php echo $i; ?>" <?php echo (isset($student['class_level']) && $student['class_level'] == "Form $i") ? 'selected' : ''; ?>>
                                                Form <?php echo $i; ?>
                                            </option>
                                            <?php endfor; ?>
                                        </optgroup>
                                    </select>
                                    <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <label for="admission_year" class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Admission Year</label>
                                <div class="relative">
                                    <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                                    <input type="text" id="admission_year" name="admission_year" required
                                           value="<?php echo $student['admission_year'] ?? '2024/2025'; ?>"
                                           placeholder="e.g. 2024/2025"
                                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">
                                    <?php echo $action === 'add' ? 'Access Password' : 'Reset Password (Optional)'; ?>
                                </label>
                                <div class="relative">
                                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                                    <input type="password" id="password" name="password" 
                                           <?php echo $action === 'add' ? 'required' : ''; ?>
                                           class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-300"
                                           placeholder="<?php echo $action === 'add' ? 'Set initial password' : 'Enter new to change'; ?>">
                                </div>
                                <?php if ($action === 'add'): ?>
                                <p class="text-[10px] text-slate-400 font-medium pl-1">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters required</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($action === 'edit' && isset($student['reg_no'])): ?>
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-slate-200 text-slate-500 flex items-center justify-center">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div>
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">System Registration ID</label>
                                <p class="text-base font-black text-slate-900 font-mono tracking-wider"><?php echo $student['reg_no']; ?></p>
                            </div>
                            <span class="ml-auto px-3 py-1 bg-slate-200 text-slate-600 rounded-full text-[10px] font-black uppercase tracking-widest">Immutable</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-end gap-3">
                            <a href="students.php" class="px-6 py-3 rounded-xl font-bold text-sm text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition-all">
                                Cancel
                            </a>
                            <button type="submit" class="premium-btn px-8 py-3 rounded-xl font-bold text-sm shadow-xl shadow-blue-600/20">
                                <?php echo $action === 'add' ? 'Complete Registration' : 'Save Changes'; ?>
                            </button>
                        </div>
                    </form>
                    
                    <?php if ($action === 'edit' && isset($student['reg_no'])): ?>
                    <div class="mt-8 pt-8 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-sm font-black text-slate-900">Profile Avatar</h3>
                                <p class="text-xs text-slate-500 mt-1">Upload a professional photo for identification</p>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 dashed-border">
                            <form action="../includes/upload_avatar.php" method="post" enctype="multipart/form-data" class="flex items-center gap-4">
                                <input type="hidden" name="target_role" value="student" />
                                <input type="hidden" name="target_id" value="<?php echo $student['id']; ?>" />
                                <input type="file" name="avatar" accept="image/*" class="block w-full text-xs text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-xs file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100" />
                                <button type="submit" class="shrink-0 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-all">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
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
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <!-- Glass Header -->
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Student Management</h1>
                    <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold">Students</span>
                    </nav>
                </div>
                <a href="students.php?action=add" class="premium-btn flex items-center gap-2 text-xs">
                    <i class="fas fa-plus"></i>
                    <span>Register Student</span>
                </a>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-8">
            <!-- Search and Filter -->
            <div class="dashboard-card mb-8">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
                    <input type="hidden" name="action" value="list">
                    
                    <div class="md:col-span-5">
                        <label for="search" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Search Directory</label>
                        <div class="relative group">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Search by name or reg no..."
                                   class="w-full pl-10 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-900 font-medium text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm">
                        </div>
                    </div>
                    
                    <div class="md:col-span-4">
                        <label for="year" class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Class Level</label>
                        <div class="relative">
                            <i class="fas fa-filter absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select id="year" name="year" class="w-full pl-10 pr-4 py-3 bg-slate-50/50 border border-slate-200 rounded-xl text-slate-900 font-medium text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all shadow-sm appearance-none cursor-pointer">
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
                    </div>
                    
                    <div class="md:col-span-3">
                        <button type="submit" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold text-sm hover:bg-slate-800 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Students Table -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-white/50 shadow-xl shadow-slate-200/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Student Identity</th>
                                <th>Academic Name</th>
                                <th>Class Level</th>
                                <th>Joined Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($student = $students->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="!py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <?php if (!empty($student['avatar'])): ?>
                                                <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>" alt="avatar" class="w-10 h-10 rounded-xl object-cover shadow-sm ring-2 ring-white" />
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-sm font-black shadow-lg shadow-blue-500/20 ring-2 ring-white">
                                                    <?php echo htmlspecialchars(substr($student['name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-400 rounded-full border-2 border-white"></div>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">REG NO</span>
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-xs font-black tracking-wide">
                                                <?php echo $student['reg_no']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="!py-4">
                                    <div class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors"><?php echo $student['name']; ?></div>
                                </td>
                                <td class="!py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                                        <?php echo strpos($student['class_level'] ?? '', 'Form') !== false ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600'; ?>">
                                        <?php echo htmlspecialchars($student['class_level'] ?? 'Year ' . $student['year_of_study']); ?>
                                    </span>
                                </td>
                                <td class="!py-4">
                                    <span class="text-xs font-bold text-slate-500">
                                        <?php echo date('M j, Y', strtotime($student['created_at'])); ?>
                                    </span>
                                </td>
                                <td class="!py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-2 group-hover:translate-x-0">
                                        <a href="students.php?action=view&id=<?php echo $student['id']; ?>" 
                                           class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-500 hover:text-white transition-all shadow-sm"
                                           title="View Profile">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" 
                                           class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-all shadow-sm"
                                           title="Edit Details">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <a href="students.php?action=delete&id=<?php echo $student['id']; ?>" 
                                           class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm"
                                           onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.')"
                                           title="Delete Account">
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
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}

function displayStudentView($student, $results) {
    $page_title = "Student Details";
    include '../includes/header.php';
    include '../includes/admin_sidebar.php';
    ?>
    
    <div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
        <!-- Glass Header -->
        <div class="glass-header sticky top-0 z-20 mb-8">
            <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Student Profile</h1>
                    <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                        <span>Admin</span>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <a href="students.php" class="hover:text-blue-600 transition-colors">Students</a>
                        <i class="fas fa-chevron-right text-[10px]"></i>
                        <span class="text-blue-600 font-bold"><?php echo htmlspecialchars($student['reg_no']); ?></span>
                    </nav>
                </div>
                <a href="students.php" class="px-4 py-2 bg-white text-slate-600 rounded-xl font-bold text-xs border border-slate-200 hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Directory</span>
                </a>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Identity Card (Left Column) -->
                <div class="lg:col-span-1 space-y-8">
                    <div class="dashboard-card bg-gradient-to-br from-blue-600 to-indigo-700 text-white border-none relative overflow-hidden">
                        <!-- Decorative bg patterns -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-black/10 rounded-full blur-2xl -ml-10 -mb-10"></div>
                        
                        <div class="relative z-10 flex flex-col items-center text-center pt-4">
                            <div class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur-sm p-1 shadow-inner mb-4">
                                <?php if (!empty($student['avatar'])): ?>
                                    <img src="../assets/uploads/avatars/<?php echo htmlspecialchars($student['avatar']); ?>" alt="avatar" class="w-full h-full rounded-xl object-cover shadow-sm bg-white" />
                                <?php else: ?>
                                    <div class="w-full h-full rounded-xl bg-white text-blue-600 flex items-center justify-center text-3xl font-black">
                                        <?php echo htmlspecialchars(substr($student['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <h2 class="text-xl font-black tracking-tight mb-1"><?php echo htmlspecialchars($student['name']); ?></h2>
                            <p class="text-blue-100 font-medium text-sm mb-6"><?php echo htmlspecialchars($student['reg_no']); ?></p>
                            
                            <div class="w-full grid grid-cols-2 gap-4 border-t border-white/10 pt-6">
                                <div>
                                    <span class="block text-[10px] uppercase tracking-widest text-blue-200 mb-1">Class</span>
                                    <span class="font-bold text-white"><?php echo htmlspecialchars($student['class_level'] ?? 'N/A'); ?></span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase tracking-widest text-blue-200 mb-1">Admitted</span>
                                    <span class="font-bold text-white"><?php echo htmlspecialchars($student['admission_year']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="dashboard-card">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" 
                               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-user-edit text-xs"></i>
                                </div>
                                <span class="font-bold text-xs">Edit Profile</span>
                                <i class="fas fa-chevron-right ml-auto text-[10px] opacity-50"></i>
                            </a>
                            
                            <a href="results.php?action=add&student_id=<?php echo $student['id']; ?>" 
                               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-plus-circle text-xs"></i>
                                </div>
                                <span class="font-bold text-xs">Add Result</span>
                                <i class="fas fa-chevron-right ml-auto text-[10px] opacity-50"></i>
                            </a>
                            
                            <a href="../admin/export/result_pdf.php?student_id=<?php echo $student['id']; ?>" target="_blank"
                               class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all group">
                                <div class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i class="fas fa-file-pdf text-xs"></i>
                                </div>
                                <span class="font-bold text-xs">Generate Report PDF</span>
                                <i class="fas fa-chevron-right ml-auto text-[10px] opacity-50"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4">System Info</h3>
                        <div class="flex items-center justify-between text-xs py-2 border-b border-slate-50">
                            <span class="font-medium text-slate-500">Created At</span>
                            <span class="font-bold text-slate-700"><?php echo date('d M Y', strtotime($student['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-xs py-2">
                             <span class="font-medium text-slate-500">Status</span>
                             <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[10px] font-bold">Active</span>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Results (Right Column - Wider) -->
                <div class="lg:col-span-2">
                    <div class="dashboard-card h-full">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Academic History</h3>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Performance Records</p>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">
                                <?php echo $results->num_rows; ?> Records Found
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <?php if ($results->num_rows > 0): ?>
                            <table class="table-modern">
                                <thead>
                                    <tr>
                                        <th>Term / Semester</th>
                                        <th>Subject</th>
                                        <th class="text-center">Marks</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">QP</th>
                                        <th class="text-right">Exam Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($result = $results->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="!py-4 text-xs font-bold text-slate-600">
                                            Term <?php echo $result['term']; ?>
                                        </td>
                                        <td class="!py-4">
                                            <div class="flex flex-col">
                                                <span class="font-bold text-slate-900 text-sm"><?php echo $result['subject_name']; ?></span>
                                                <span class="text-[10px] font-black text-slate-400 uppercase"><?php echo $result['subject_code']; ?></span>
                                            </div>
                                        </td>
                                        <td class="!py-4 text-center">
                                            <span class="font-mono font-bold text-slate-700"><?php echo $result['marks_obtained']; ?></span>
                                        </td>
                                        <td class="!py-4 text-center">
                                            <span class="w-8 h-8 rounded-lg inline-flex items-center justify-center font-black text-xs
                                                <?php echo $result['grade'] === 'F' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                                                <?php echo $result['grade']; ?>
                                            </span>
                                        </td>
                                        <td class="!py-4 text-center text-xs font-bold text-slate-500">
                                            <?php echo $result['grade_point']; ?>
                                        </td>
                                        <td class="!py-4 text-right text-xs font-medium text-slate-400">
                                            <?php echo date('M d, Y', strtotime($result['exam_date'])); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-clipboard-list text-slate-300 text-2xl"></i>
                                </div>
                                <h4 class="text-slate-900 font-bold mb-1">No Academic Records</h4>
                                <p class="text-slate-500 text-sm mb-6 max-w-xs">This student hasn't been assigned any exam results yet.</p>
                                <a href="results.php?action=add&student_id=<?php echo $student['id']; ?>" 
                                   class="premium-btn px-6 py-2 rounded-lg text-xs font-bold shadow-lg shadow-blue-500/20">
                                    Add First Result
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php';
}
?>