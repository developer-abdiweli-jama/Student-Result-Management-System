<?php
// login.php
require_once 'config/database.php';
require_once 'config/constants.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';
require_once 'includes/auth.php';


// session may already be started by included files (session.php or auth.php), so check first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isLoggedIn()) {
    // use the auth helper which reads the session safely
    $role = currentUserRole();
    if ($role === ROLE_ADMIN) {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($role === ROLE_STUDENT) {
        header("Location: student/dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'] ?? '';

    $conn = getDBConnection();

    // === Try admin login ===
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $adminResult = $stmt->get_result();
    $stmt->close();

    if ($adminResult->num_rows === 1) {
        $admin = $adminResult->fetch_assoc();
        if (verifyPassword($password, $admin['password_hash'])) {
            loginUser($admin, ROLE_ADMIN);
            header("Location: admin/dashboard.php");
            exit();
        }
    }

    // === Try student login ===
    $stmt = $conn->prepare("SELECT id, reg_no, name, password_hash FROM students WHERE reg_no = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    $stmt->close();

    if ($studentResult->num_rows === 1) {
        $student = $studentResult->fetch_assoc();
        if (verifyPassword($password, $student['password_hash'])) {
            loginUser($student, ROLE_STUDENT);
            header("Location: student/dashboard.php");
            exit();
        }
    }

    // === Try teacher login ===
    $stmt = $conn->prepare("SELECT id, reg_no, name, password_hash FROM teachers WHERE reg_no = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $teacherResult = $stmt->get_result();
    $stmt->close();

    if ($teacherResult && $teacherResult->num_rows === 1) {
        $teacher = $teacherResult->fetch_assoc();
        if (verifyPassword($password, $teacher['password_hash'])) {
            loginUser($teacher, ROLE_TEACHER);
            header("Location: teacher/dashboard.php");
            exit();
        }
    }

    // If neither login worked
    $error = "Invalid username or password.";
}

$page_title = "Login";
include 'includes/header.php';
?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h2>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username / Reg No</label>
                <input type="text" id="username" name="username" required
                       class="mt-1 block w-full border border-gray-300 px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required
                       class="mt-1 block w-full border border-gray-300 px-3 py-2 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex justify-between items-center">
                <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md shadow-sm transition duration-200">
                Login
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

