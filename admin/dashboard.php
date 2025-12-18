<?php
// admin/dashboard.php
require_once '../includes/middleware/admin_auth.php';
require_once '../config/database.php';

$conn = getDBConnection();

// Get statistics
// Get statistics
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalResults = $conn->query("SELECT COUNT(*) as count FROM results")->fetch_assoc()['count'];
$totalTeachers = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
$pendingRequests = $conn->query("SELECT COUNT(*) as count FROM teacher_assignments WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent students
$recentStudents = $conn->query("
    SELECT reg_no, name, year_of_study, created_at 
    FROM students 
    ORDER BY created_at DESC 
    LIMIT 5
");

// Get recent results
$recentResults = $conn->query("
    SELECT s.reg_no, s.name, sub.subject_name, r.marks_obtained, r.grade
    FROM results r
    JOIN students s ON r.student_id = s.id
    JOIN subjects sub ON r.subject_id = sub.id
    ORDER BY r.created_at DESC 
    LIMIT 5
");

// Analytics: Grade distribution
$gradeDistRes = $conn->query("SELECT grade, COUNT(*) as count FROM results GROUP BY grade ORDER BY grade");
$gradeLabels = [];
$gradeData = [];
while($row = $gradeDistRes->fetch_assoc()) {
    $gradeLabels[] = $row['grade'];
    $gradeData[] = (int)$row['count'];
}

// Analytics: Student enrollment by year
$enrollmentRes = $conn->query("SELECT year_of_study, COUNT(*) as count FROM students GROUP BY year_of_study ORDER BY year_of_study");
$enrollmentLabels = [];
$enrollmentData = [];
while($row = $enrollmentRes->fetch_assoc()) {
    $enrollmentLabels[] = "Year " . $row['year_of_study'];
    $enrollmentData[] = (int)$row['count'];
}

$conn->close();

$page_title = "Admin Dashboard";
$page_scripts = ['admin/dashboard.js'];
include '../includes/header.php';
?>

<!-- Admin Sidebar -->
<?php include '../includes/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 flex-1 bg-[#f8fafc] min-h-screen">
    <!-- Admin Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Admin Dashboard</h1>
                <p class="text-slate-500 font-medium flex items-center gap-2 mt-1">
                    <span class="bg-blue-600 w-2 h-2 rounded-full animate-pulse"></span>
                    Welcome back, <span class="text-slate-900 font-bold"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">System Operational</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <!-- Statistics Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
            <!-- Total Students -->
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-blue-50 text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Total Students</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $totalStudents; ?></p>
            </div>

            <!-- Total Teachers -->
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-indigo-50 text-indigo-600 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Total Teachers</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $totalTeachers; ?></p>
            </div>

            <!-- Pending Requests -->
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-orange-50 text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-clock"></i>
                    </div>
                    <?php if ($pendingRequests > 0): ?>
                        <span class="px-2 py-1 bg-rose-500 text-white text-[10px] font-black rounded-lg animate-bounce">URGENT</span>
                    <?php endif; ?>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Pending Requests</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $pendingRequests; ?></p>
            </div>

            <!-- Total Results -->
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-500">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-slate-500">Total Results</h3>
                <p class="text-4xl font-black text-slate-900 mt-2"><?php echo $totalResults; ?></p>
            </div>
        </div>

        <!-- Analytics & Trends -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <div class="dashboard-card">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Grade Distribution</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Academic Performance Overview</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-chart-pie text-xs"></i>
                    </div>
                </div>
                <div class="h-80 flex items-center justify-center relative">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Student Enrollment</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Institutional Growth Metrics</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-chart-bar text-xs"></i>
                    </div>
                </div>
                <div class="h-80 flex items-center justify-center relative">
                    <canvas id="enrollmentChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 pb-12">
            <!-- Recent Students -->
            <div class="dashboard-card !p-0 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Recent Students</h3>
                        <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mt-1">New Registrations</p>
                    </div>
                    <a href="students.php" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-blue-600 transition-colors">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Identity Identifier</th>
                                <th>Academic Name</th>
                                <th>Class Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($student = $recentStudents->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="!py-4">
                                    <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black tracking-widest">
                                        <?php echo $student['reg_no']; ?>
                                    </span>
                                </td>
                                <td class="!py-4 font-bold text-slate-900"><?php echo $student['name']; ?></td>
                                <td class="!py-4">
                                    <span class="text-xs font-bold text-slate-500 italic">Grade <?php echo $student['year_of_study']; ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Recent Results -->
            <div class="dashboard-card !p-0 overflow-hidden">
                <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight">Recent Results</h3>
                        <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mt-1">Latest Assessments</p>
                    </div>
                    <a href="results.php" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-emerald-600 transition-colors">View Records</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Subject</th>
                                <th>Achievement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($result = $recentResults->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="!py-4">
                                    <div class="text-[11px] font-black text-slate-900 leading-none mb-1 uppercase tracking-tight"><?php echo $result['name']; ?></div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest"><?php echo $result['reg_no']; ?></div>
                                </td>
                                <td class="!py-4 font-bold text-slate-600 italic text-xs"><?php echo $result['subject_name']; ?></td>
                                <td class="!py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="text-sm font-black text-slate-900"><?php echo $result['marks_obtained']; ?>%</div>
                                        <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-[10px] font-black
                                            <?php echo $result['grade'] === 'F' ? '!bg-rose-50 !text-rose-600' : '!bg-emerald-50 !text-emerald-600'; ?>">
                                            <?php echo $result['grade']; ?>
                                        </span>
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
</div>

<!-- Inject Data for JS -->
<script>
    window.chartData = {
        gradeLabels: <?php echo json_encode($gradeLabels); ?>,
        gradeData: <?php echo json_encode($gradeData); ?>,
        enrollmentLabels: <?php echo json_encode($enrollmentLabels); ?>,
        enrollmentData: <?php echo json_encode($enrollmentData); ?>
    };
</script>

<?php include '../includes/footer.php'; ?>