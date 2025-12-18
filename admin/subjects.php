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

// Fetch Subjects
$result = $conn->query("SELECT * FROM subjects ORDER BY class_level, subject_code");

$page_title = 'Manage Subjects';
include __DIR__ . '/../includes/header.php';
?>

<?php include '../includes/admin_sidebar.php'; ?>

<div class="lg:ml-64 flex-1 p-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Subjects</h1>
            <p class="text-gray-600">Manage curriculum and courses</p>
        </div>
        <button onclick="openModal('create')" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center shadow-md transition duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Subject
        </button>
    </div>

    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg flex items-center <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credits</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600"><?php echo htmlspecialchars($row['subject_code']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['subject_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['class_level']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['credits']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick='openModal("update", <?php echo json_encode($row); ?>)' class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                <form method="post" class="inline-block" onsubmit="return confirm('Delete this subject?');">
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="subjectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 id="modalTitle" class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Subject</h3>
        <form method="post" id="subjectForm">
            <input type="hidden" name="action" id="formAction" value="create" />
            <input type="hidden" name="id" id="subjectId" value="" />
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Code</label>
                    <input name="subject_code" id="subject_code" placeholder="e.g. MATH101" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject Name</label>
                    <input name="subject_name" id="subject_name" placeholder="Mathematics" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Class Level</label>
                    <select name="class_level" id="class_level" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Credits</label>
                    <input type="number" step="0.5" name="credits" id="credits" value="3.0" class="w-full border p-2 rounded focus:ring-blue-500 focus:border-blue-500" required />
                </div>
            </div>
            
            <div class="items-center px-4 py-3 mt-4 text-right">
                <button type="button" onclick="document.getElementById('subjectModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded mr-2 hover:bg-gray-300">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save</button>
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
        title.innerText = 'Add Subject';
        action.value = 'create';
    }
    
    modal.classList.remove('hidden');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
