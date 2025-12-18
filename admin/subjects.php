<?php
require_once __DIR__ . '/../includes/middleware/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

$message = '';
$messageType = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $code = strtoupper(trim($_POST['subject_code']));
        $name = trim($_POST['subject_name']);
        $credits = floatval($_POST['credits']);
        $class_level = trim($_POST['class_level']);
        
        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, credits, class_level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssds', $code, $name, $credits, $class_level);
            if ($stmt->execute()) {
                $message = 'Subject created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error creating subject: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        } else {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=?, credits=?, class_level=? WHERE id=?");
            $stmt->bind_param('ssdsi', $code, $name, $credits, $class_level, $id);
            if ($stmt->execute()) {
                $message = 'Subject updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Error updating subject: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $message = 'Subject deleted.';
            $messageType = 'success';
        } else {
            // Check for constraint violation (results, assignments)
            if ($conn->errno === 1451) {
                $message = 'Cannot delete subject: It has associated results or existing assignments.';
            } else {
                $message = 'Error deleting subject: ' . $stmt->error;
            }
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Fetch Subjects with optional search/filter
$search = $_GET['search'] ?? '';
$class_filter = $_GET['class_level'] ?? '';

$query = "SELECT * FROM subjects WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (subject_name LIKE ? OR subject_code LIKE ?)";
    $term = "%$search%";
    $params[] = $term;
    $params[] = $term;
    $types .= "ss";
}

if (!empty($class_filter)) {
    $query .= " AND class_level = ?";
    $params[] = $class_filter;
    $types .= "s";
}

$query .= " ORDER BY class_level, subject_code";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'Manage Subjects';
include __DIR__ . '/../includes/header.php';
include '../includes/admin_sidebar.php';
?>

<div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
    <!-- Glass Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Curriculum</h1>
                <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                    <span>Admin</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span>Academics</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600 font-bold">Subjects</span>
                </nav>
            </div>
            <button onclick="openModal('create')" 
                    class="premium-btn px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20 flex items-center gap-2 transform hover:scale-105 transition-transform">
                <i class="fas fa-plus-circle"></i> Add Subject
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

        <!-- Search & Filter -->
        <div class="dashboard-card mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
                    <label for="search" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Search Subjects</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                         <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Subject name or code..."
                               class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                    </div>
                </div>
                
                <div class="md:col-span-4">
                    <label for="class_level_filter" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Filter by Class</label>
                    <div class="relative">
                        <i class="fas fa-layer-group absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <select id="class_level_filter" name="class_level" class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                            <option value="">All Classes</option>
                            <?php 
                            $classes = ['Form 4', 'Form 3', 'Form 2', 'Form 1', 'Grade 8', 'Grade 7', 'Grade 6', 'Grade 5'];
                            foreach ($classes as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo $class_filter == $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="md:col-span-3">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors shadow-sm flex items-center justify-center font-bold text-sm">
                        <i class="fas fa-filter mr-2"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Subjects Table -->
        <div class="dashboard-card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table-modern w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Subject Name</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-wider">Credits</th>
                            <th class="px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                                        <?php echo htmlspecialchars($row['subject_code']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                                        <?php echo htmlspecialchars($row['class_level']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-600">
                                    <?php echo $row['credits']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button onclick='openModal("update", <?php echo json_encode($row); ?>)' 
                                                class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                                title="Edit Subject">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <form method="post" class="inline-block" onsubmit="return confirm('Delete this subject?');">
                                            <input type="hidden" name="action" value="delete" />
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                            <button type="submit" 
                                                    class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                                    title="Delete Subject">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($result->num_rows == 0): ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fas fa-book-open text-2xl opacity-50"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700">No subjects found</h3>
                <p class="text-slate-500 text-sm mt-1">Add a new subject or adjust filters.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Premium Modal -->
<div id="subjectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none data-[state=open]:opacity-100 data-[state=open]:pointer-events-auto">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-transform data-[state=open]:scale-100 overflow-hidden m-4">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 id="modalTitle" class="text-lg font-black text-slate-800">Add Subject</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form method="post" id="subjectForm" class="p-6">
            <input type="hidden" name="action" id="formAction" value="create" />
            <input type="hidden" name="id" id="subjectId" value="" />
            
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Subject Code</label>
                    <input name="subject_code" id="subject_code" placeholder="e.g. MATH101" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required />
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Subject Name</label>
                    <input name="subject_name" id="subject_name" placeholder="Mathematics" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required />
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Class Level</label>
                    <select name="class_level" id="class_level" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required>
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
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 pl-1">Credits</label>
                    <input type="number" step="0.5" name="credits" id="credits" value="3.0" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required />
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 mt-8">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-colors">Cancel</button>
                <button type="submit" class="premium-btn px-6 py-2.5 rounded-xl text-sm font-bold shadow-lg shadow-blue-500/20">
                    <i class="fas fa-check-circle mr-2"></i> Save Subject
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(mode, data = null) {
    const modal = document.getElementById('subjectModal');
    const title = document.getElementById('modalTitle');
    const action = document.getElementById('formAction');
    const idField = document.getElementById('subjectId');
    const container = modal.querySelector('div'); // The inner modal container
    
    // Reset form
    document.getElementById('subjectForm').reset();
    
    if (mode === 'update' && data) {
        title.innerText = 'Edit Subject';
        action.value = 'update';
        idField.value = data.id;
        document.getElementById('subject_code').value = data.subject_code;
        document.getElementById('subject_name').value = data.subject_name;
        document.getElementById('class_level').value = data.class_level;
        document.getElementById('credits').value = data.credits;
    } else {
        title.innerText = 'New Subject';
        action.value = 'create';
    }
    
    modal.classList.remove('hidden');
    // Allow small delay for transition
    requestAnimationFrame(() => {
        modal.setAttribute('data-state', 'open');
    });
}

function closeModal() {
    const modal = document.getElementById('subjectModal');
    modal.removeAttribute('data-state');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200); // clear after transition
}

// Close on click outside
document.getElementById('subjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
