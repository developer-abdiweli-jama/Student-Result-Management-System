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

<div class="fixed inset-0 -z-10">
    <img src="assets/images/login_bg.png" alt="Background" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900/40 via-transparent to-indigo-900/40 backdrop-blur-[2px]"></div>
</div>

<div class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-10 rounded-[2.5rem] shadow-2xl border border-white/50 animate-modal-in">
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-3xl shadow-xl shadow-blue-500/20 mb-6">
                <i class="fas fa-graduation-cap text-white text-3xl"></i>
            </div>
            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Welcome Back</h2>
            <p class="text-slate-500 font-medium mt-2">Secure access to Hirgal Nexus SRMIS</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-8 p-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-800 flex items-center gap-3 animate-slide-in">
                <i class="fas fa-exclamation-circle text-rose-500"></i>
                <p class="text-sm font-bold"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-6">
            <div class="space-y-2">
                <label for="username" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Identity Identifier</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="text" id="username" name="username" required placeholder="Reg No or Username"
                           class="block w-full bg-slate-50/50 border-slate-200/50 pl-12 pr-4 py-4 rounded-2xl font-bold text-slate-900 placeholder:text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Access Key</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="password" id="password" name="password" required placeholder="\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022"
                           class="block w-full bg-slate-50/50 border-slate-200/50 pl-12 pr-4 py-4 rounded-2xl font-bold text-slate-900 placeholder:text-slate-300 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none">
                </div>
            </div>

            <div class="flex justify-end pr-2">
                <a href="forgot_password.php" class="text-xs font-black text-blue-600 hover:text-blue-700 uppercase tracking-widest transition-colors">Recovery Assistance</a>
            </div>

            <button type="submit"
                    class="w-full premium-gradient-bg text-white py-5 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-blue-500/20 hover:shadow-2xl hover:shadow-blue-500/40 hover:scale-[1.02] active:scale-[0.98] transition-all">
                Authorize Access
            </button>
        </form>

        <div class="mt-12 pt-8 border-t border-slate-100 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center justify-center gap-2">
                <i class="fas fa-shield-alt text-emerald-500"></i>
                End-to-End Encrypted Authority
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

