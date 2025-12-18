<?php
// admin/reports.php
require_once '../includes/middleware/admin_auth.php';
require_once '../config/database.php';

$conn = getDBConnection();

// Get statistics for reports
$totalStudents = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
$totalResults = $conn->query("SELECT COUNT(*) as count FROM results")->fetch_assoc()['count'];

// Semester-wise statistics -> Term-wise statistics
$termStats = $conn->query("
    SELECT term, 
           COUNT(*) as total_results,
           AVG(marks_obtained) as avg_marks,
           AVG(grade_point) as avg_gpa
    FROM results 
    GROUP BY term 
    ORDER BY term
");

// Subject-wise performance
$subjectStats = $conn->query("
    SELECT s.subject_code, s.subject_name, s.class_level,
           COUNT(r.id) as total_students,
           AVG(r.marks_obtained) as avg_marks,
           AVG(r.grade_point) as avg_gpa,
           SUM(CASE WHEN r.grade = 'F' THEN 1 ELSE 0 END) as failed_count
    FROM subjects s
    LEFT JOIN results r ON s.id = r.subject_id
    GROUP BY s.id, s.subject_code, s.subject_name, s.class_level
    ORDER BY s.class_level, s.subject_code
");

// Grade distribution
$gradeDistribution = $conn->query("
    SELECT grade, COUNT(*) as count,
           ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM results)), 2) as percentage
    FROM results 
    GROUP BY grade 
    ORDER BY 
        CASE grade 
            WHEN 'A' THEN 1
            WHEN 'A-' THEN 2
            WHEN 'B+' THEN 3
            WHEN 'B' THEN 4
            WHEN 'B-' THEN 5
            WHEN 'C+' THEN 6
            WHEN 'C' THEN 7
            WHEN 'D' THEN 8
            WHEN 'F' THEN 9
            ELSE 10
        END
");

// Yearly performance trends
$yearlyPerformance = $conn->query("
    SELECT academic_year, 
           AVG(grade_point) as avg_gpa,
           AVG(marks_obtained) as avg_marks
    FROM results 
    GROUP BY academic_year 
    ORDER BY academic_year ASC
");
$yearlyLabels = [];
$yearlyGPA = [];
while($row = $yearlyPerformance->fetch_assoc()) {
    $yearlyLabels[] = $row['academic_year'];
    $yearlyGPA[] = round($row['avg_gpa'], 2);
}

// Data for Grade Distribution Chart
$gradeLabels = [];
$gradeCounts = [];
$gradeDistribution->data_seek(0);
while($row = $gradeDistribution->fetch_assoc()) {
    $gradeLabels[] = $row['grade'];
    $gradeCounts[] = (int)$row['count'];
}

$page_title = "Reports & Analytics";
$page_scripts = ['admin/reports.js'];
include '../includes/header.php';
?>
<?php include '../includes/admin_sidebar.php'; ?>

<div class="lg:ml-64 flex-1 bg-slate-50 min-h-screen pb-12">
    <!-- Glass Header -->
    <div class="glass-header sticky top-0 z-20 mb-8">
        <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Analytics & Reports</h1>
                <nav class="flex items-center gap-2 mt-1 text-xs font-medium text-slate-500">
                    <span>Admin</span>
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-blue-600 font-bold">Academic Insights</span>
                </nav>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex bg-slate-100 p-1 rounded-xl gap-1">
                    <button onclick="exportToExcel()" class="px-4 py-2 bg-white text-emerald-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-emerald-50 transition-all flex items-center gap-2 shadow-sm border border-slate-200">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <button onclick="exportToPDF()" class="px-4 py-2 bg-white text-rose-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-rose-50 transition-all flex items-center gap-2 shadow-sm border border-slate-200">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                </div>
                <div class="h-8 w-[1px] bg-slate-200 mx-2"></div>
                <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <i class="fas fa-calendar-alt text-blue-500"></i>
                    <?php echo date('F Y'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Students -->
            <div class="dashboard-card group hover:scale-[1.02] cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner border border-blue-100 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Total Enrollment</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-900"><?php echo number_format($totalStudents); ?></p>
                    <span class="text-[10px] font-bold text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">ACTIVE</span>
                </div>
            </div>

            <!-- Results Published -->
            <div class="dashboard-card group hover:scale-[1.02] cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner border border-indigo-100 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                </div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Results Issued</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-900"><?php echo number_format($totalResults); ?></p>
                </div>
            </div>

            <!-- Average GPA -->
            <div class="dashboard-card group hover:scale-[1.02] cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner border border-emerald-100 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Mean GPA Score</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-900">
                        <?php 
                        $avgGPA = $conn->query("SELECT AVG(grade_point) as avg_gpa FROM results")->fetch_assoc()['avg_gpa'] ?? 0;
                        echo formatGPA($avgGPA);
                        ?>
                    </p>
                </div>
            </div>

            <!-- Failure Rate -->
            <div class="dashboard-card group hover:scale-[1.02] cursor-default">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner border border-rose-100 group-hover:bg-rose-600 group-hover:text-white transition-all">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">At Risk Students</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <p class="text-3xl font-black text-slate-900">
                        <?php
                        $failRate = $conn->query("SELECT ROUND((SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as rate FROM results")->fetch_assoc()['rate'] ?? 0;
                        echo $failRate . '%';
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Analytical Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="dashboard-card shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Yearly GPA Trends</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Academic Progress Over Time</p>
                    </div>
                    <div class="stats-icon-wrapper !w-10 !h-10 bg-blue-50 text-blue-600">
                        <i class="fas fa-chart-line text-xs"></i>
                    </div>
                </div>
                <div class="h-64 relative">
                    <canvas id="yearlyChart"></canvas>
                </div>
            </div>

            <div class="dashboard-card shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Grade Distribution</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Current Population Analysis</p>
                    </div>
                    <div class="stats-icon-wrapper !w-10 !h-10 bg-indigo-50 text-indigo-600">
                        <i class="fas fa-chart-pie text-xs"></i>
                    </div>
                </div>
                <div class="h-64 relative">
                    <canvas id="gradeChart"></canvas>
                </div>
            </div>
        </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
         <!-- Term Statistics -->
         <div class="lg:col-span-1">
             <div class="dashboard-card p-0 overflow-hidden h-full">
                <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Term Metrics</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-modern w-full">
                        <thead class="bg-slate-50/30">
                            <tr>
                                <th class="px-6 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Term</th>
                                <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Avg GPA</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php 
                            $termStats->data_seek(0);
                            while($term = $termStats->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-slate-900">Term <?php echo $term['term']; ?></div>
                                    <div class="text-[10px] font-bold text-slate-400"><?php echo $term['total_results']; ?> Entries</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black
                                        <?php echo $term['avg_gpa'] >= 3.0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                               ($term['avg_gpa'] >= 2.0 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-rose-50 text-rose-600 border border-rose-100'); ?>">
                                        <?php echo formatGPA($term['avg_gpa']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
         </div>

         <!-- Grade Distribution -->
         <div class="lg:col-span-2">
             <div class="dashboard-card p-0 overflow-hidden h-full">
                <div class="px-8 py-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Grade Distribution</h3>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                        <?php 
                        $gradeDistribution->data_seek(0);
                        while($grade = $gradeDistribution->fetch_assoc()): ?>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center text-center group hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all cursor-default">
                             <span class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-black mb-2
                                <?php echo in_array($grade['grade'], ['A', 'A-', 'B+']) ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                           ($grade['grade'] === 'F' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-blue-50 text-blue-600 border border-blue-100'); ?>">
                                <?php echo $grade['grade']; ?>
                             </span>
                             <div class="text-xl font-black text-slate-900"><?php echo $grade['count']; ?></div>
                             <div class="text-[10px] font-bold text-slate-400 uppercase mt-0.5"><?php echo $grade['percentage']; ?>%</div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
             </div>
         </div>
    </div>

    <!-- Subject-wise Performance -->
    <div class="dashboard-card p-0 overflow-hidden shadow-xl shadow-slate-200/50">
        <div class="px-8 py-6 border-b border-slate-100 bg-white flex justify-between items-center">
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Departmental Performance</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Subject-level analysis across classes</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="table-modern w-full">
                <thead class="bg-slate-50 text-slate-400">
                    <tr>
                        <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest">Subject</th>
                        <th class="px-8 py-4 text-left text-[10px] font-black uppercase tracking-widest">Class</th>
                        <th class="px-8 py-4 text-center text-[10px] font-black uppercase tracking-widest">Students</th>
                        <th class="px-8 py-4 text-center text-[10px] font-black uppercase tracking-widest">Mean Marks</th>
                        <th class="px-8 py-4 text-center text-[10px] font-black uppercase tracking-widest">Mean GPA</th>
                        <th class="px-8 py-4 text-right text-[10px] font-black uppercase tracking-widest">Failures</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while($subject = $subjectStats->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5 whitespace-nowrap">
                            <div class="text-sm font-bold text-slate-900"><?php echo $subject['subject_name']; ?></div>
                            <div class="text-[10px] font-black text-slate-400 uppercase mt-0.5"><?php echo $subject['subject_code']; ?></div>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap">
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase">
                                <?php echo $subject['class_level']; ?>
                            </span>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-center text-sm font-bold text-slate-600">
                            <?php echo $subject['total_students']; ?>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-center text-sm font-black text-slate-700">
                            <?php echo $subject['avg_marks'] ? formatGPA($subject['avg_marks']) . '%' : 'N/A'; ?>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-center">
                            <?php if ($subject['avg_gpa']): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black
                                <?php echo $subject['avg_gpa'] >= 3.0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 
                                       ($subject['avg_gpa'] >= 2.0 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-rose-50 text-rose-600 border border-rose-100'); ?>">
                                <?php echo formatGPA($subject['avg_gpa']); ?>
                            </span>
                            <?php else: ?>
                            <span class="text-[10px] font-black text-slate-300">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-5 whitespace-nowrap text-right">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-black
                                <?php echo $subject['failed_count'] > 0 ? 'bg-rose-50 text-rose-600 border border-rose-100 animate-pulse' : 'bg-slate-50 text-slate-400 border border-slate-100'; ?>">
                                <?php echo $subject['failed_count']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inject Data for JS -->
<script>
    window.chartData = {
        yearlyLabels: <?php echo json_encode($yearlyLabels); ?>,
        yearlyGPA: <?php echo json_encode($yearlyGPA); ?>,
        gradeLabels: <?php echo json_encode($gradeLabels); ?>,
        gradeCounts: <?php echo json_encode($gradeCounts); ?>
    };
</script>

<?php include '../includes/footer.php'; ?>
<?php
// close connection if still open
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>