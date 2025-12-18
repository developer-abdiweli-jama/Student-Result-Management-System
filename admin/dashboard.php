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
<div class="lg:ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
        <p class="text-gray-600">Welcome back, <?php echo $_SESSION['username']; ?></p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Students -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Total Students</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $totalStudents; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Teachers -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Total Teachers</h3>
                    <p class="text-2xl font-bold text-indigo-600"><?php echo $totalTeachers; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pending Requests</h3>
                    <p class="text-2xl font-bold text-orange-600"><?php echo $pendingRequests; ?></p>
                </div>
            </div>
        </div>
        
        <!-- Published Results -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Results</h3>
                    <p class="text-2xl font-bold text-green-600"><?php echo $totalResults; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-blue-500"></i> Grade Distribution
            </h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="gradeChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-indigo-500"></i> Student Enrollment
            </h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="enrollmentChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Students -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Students</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reg No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($student = $recentStudents->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600"><?php echo $student['reg_no']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $student['name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Year <?php echo $student['year_of_study']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Results -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Results</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($result = $recentResults->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $result['name']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $result['reg_no']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $result['subject_name']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $result['marks_obtained']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $result['grade'] === 'F' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $result['grade']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
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