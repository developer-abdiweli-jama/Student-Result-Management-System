<?php
// student/dashboard.php
require_once '../includes/middleware/student_auth.php';
require_once '../config/database.php';

$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

// Get student information
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get all results for the student
$results = $conn->query("
    SELECT r.*, sub.subject_code, sub.subject_name, sub.credits, sub.class_level as sub_class_level
    FROM results r
    LEFT JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = $student_id
    ORDER BY r.academic_year DESC, sub.class_level DESC, r.term, sub.subject_code
");

// Calculate statistics
$grouped_resultsByClass = []; // New grouping: [class_level][academic_year][term]
$grouped_resultsByYear = [];  // Keep for stats
$total_credits = 0;
$total_grade_points = 0;

while ($result = $results->fetch_assoc()) {
    $year = $result['academic_year'] ?? 'Unknown Year';
    $class_level = $result['sub_class_level'] ?? 'Unknown Class';
    $term = $result['term'];
    
    // Grouping by Class Level for detailed view
    if (!isset($grouped_resultsByClass[$class_level])) {
        $grouped_resultsByClass[$class_level] = [];
    }
    if (!isset($grouped_resultsByClass[$class_level][$year])) {
        $grouped_resultsByClass[$class_level][$year] = [];
    }
    if (!isset($grouped_resultsByClass[$class_level][$year][$term])) {
        $grouped_resultsByClass[$class_level][$year][$term] = [
            'total_credits' => 0,
            'total_grade_points' => 0,
            'subjects' => []
        ];
    }
    
    $grouped_resultsByClass[$class_level][$year][$term]['total_credits'] += $result['credits'];
    $grouped_resultsByClass[$class_level][$year][$term]['total_grade_points'] += ($result['grade_point'] * $result['credits']);
    $grouped_resultsByClass[$class_level][$year][$term]['subjects'][] = $result;

    // Grouping by Year for progression check (backwards compatibility for that logic)
    if (!isset($grouped_resultsByYear[$year])) {
        $grouped_resultsByYear[$year] = [];
    }
    if (!isset($grouped_resultsByYear[$year][$term])) {
        $grouped_resultsByYear[$year][$term] = [
            'total_credits' => 0,
            'total_grade_points' => 0,
            'subjects' => []
        ];
    }
    $grouped_resultsByYear[$year][$term]['total_credits'] += $result['credits'];
    $grouped_resultsByYear[$year][$term]['total_grade_points'] += ($result['grade_point'] * $result['credits']);
    $grouped_resultsByYear[$year][$term]['subjects'][] = $result;
    
    $total_credits += $result['credits'];
    $total_grade_points += ($result['grade_point'] * $result['credits']);
}

// Calculate CGPA
$cgpa = $total_credits > 0 ? $total_grade_points / $total_credits : 0;

// Determine if student passed their last academic year (Avg of Term 1 & 2 > 1.5)
$last_academic_year = '';
$can_choose_stream = false;
if (!empty($grouped_resultsByYear)) {
    // For progression check, use the year-based grouping
    foreach ($grouped_resultsByYear as $year => $terms) {
        if (count($terms) >= 2) {
            $year_credits = 0;
            $year_gp = 0;
            foreach ($terms as $term_data) {
                $year_credits += $term_data['total_credits'];
                $year_gp += $term_data['total_grade_points'];
            }
            $year_avg_gpa = $year_credits > 0 ? $year_gp / $year_credits : 0;
            
            // Check if this was Form 2 or below and they passed
            // We'll mark them as "Passed" if gpa > 1.5
            $is_passed = ($year_avg_gpa > 1.5);
            
            // If they are in Form 2 and passed, they can choose stream for Form 3
            if ($student['class_level'] === 'Form 2' && $is_passed) {
                $can_choose_stream = true;
            }
        }
    }
}

// Ensure Form 3/4 students without a stream can also choose
if (($student['class_level'] === 'Form 3' || $student['class_level'] === 'Form 4') && empty($student['stream'])) {
    $can_choose_stream = true;
}

// Get recent results
$recent_results = $conn->query("
    SELECT r.*, sub.subject_code, sub.subject_name
    FROM results r
    LEFT JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = $student_id
    ORDER BY r.academic_year DESC, r.term DESC, r.created_at DESC
    LIMIT 5
");

// Prepare GPA Trend Chart Data
$chart_labels = [];
$chart_data = [];
if (!empty($grouped_resultsByYear)) {
    // Sort years ascending for the chart
    $years_sorted = array_keys($grouped_resultsByYear);
    sort($years_sorted);
    foreach ($years_sorted as $year) {
        foreach ($grouped_resultsByYear[$year] as $term => $data) {
            $chart_labels[] = $year . ' T' . $term;
            $chart_data[] = $data['total_credits'] > 0 ? round($data['total_grade_points'] / $data['total_credits'], 2) : 0;
        }
    }
}

$conn->close();

$page_title = "Student Dashboard";
$page_scripts = ['student/dashboard.js'];
include '../includes/header.php';
?>

<div class="min-h-screen bg-[#f8fafc]">
    <!-- Student Header -->
    <div class="glass-header sticky top-0 z-30 mb-8 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center py-8 gap-6">
                <div class="flex items-center group">
                    <div class="bg-blue-600 p-4 rounded-3xl shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform duration-500">
                        <i class="fas fa-user-graduate text-white text-2xl"></i>
                    </div>
                    <div class="ml-6 text-left">
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Welcome, <?php echo htmlspecialchars($student['name']); ?></h1>
                        <p class="text-slate-500 font-medium flex items-center gap-2 mt-1">
                            <span class="bg-white/50 backdrop-blur-md px-2 py-0.5 rounded text-[10px] font-black tracking-widest uppercase border border-white/50"><?php echo htmlspecialchars($student['reg_no']); ?></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-sm font-bold text-blue-600 uppercase tracking-widest"><?php echo htmlspecialchars($student['class_level']); ?></span>
                            <?php if ($student['stream']): ?>
                                <span class="text-slate-300">•</span>
                                <span class="status-badge-premium bg-blue-100 text-blue-800 text-[10px]"><?php echo htmlspecialchars($student['stream']); ?> Stream</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="text-right bg-white/80 backdrop-blur-xl p-6 rounded-[2rem] border border-white shadow-xl flex items-center gap-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Cumulative GPA</p>
                        <p class="text-4xl font-black text-slate-900"><?php echo formatGPA($cgpa); ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-full border-4 border-blue-500 border-t-transparent animate-spin-slow"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <!-- Dashboard Content -->
        
        <?php 
        // Stream Selection for eligible students if not yet selected
        if ($can_choose_stream && empty($student['stream'])): 
        ?>
        <div class="dashboard-card !bg-blue-600 text-white mb-12 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <i class="fas fa-route text-white"></i>
                    </div>
                    <h3 class="text-xl font-black tracking-tight">Select Your Academic Stream</h3>
                </div>
                <p class="text-blue-100 font-medium mb-6">
                    <?php if ($student['class_level'] === 'Form 2'): ?>
                        Congratulations on passing Form 2! Please choose your academic track for Form 3.
                    <?php else: ?>
                        Please choose your academic track to see relevant subjects and assignments.
                    <?php endif; ?>
                </p>
                <form action="select_stream.php" method="POST" class="flex flex-wrap gap-4">
                    <button type="submit" name="stream" value="General" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-blue-50 transition-all">General</button>
                    <button type="submit" name="stream" value="Science" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-blue-50 transition-all">Science</button>
                    <button type="submit" name="stream" value="Arts" class="bg-white text-blue-600 px-6 py-3 rounded-xl font-black uppercase tracking-widest text-xs hover:bg-blue-50 transition-all">Arts</button>
                </form>
            </div>
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        </div>
        <?php endif; ?>

        <!-- Statistics Dashboard -->
        <?php 
        $subjects_count = 0;
        foreach ($grouped_resultsByClass as $class => $years) {
            foreach ($years as $year => $terms) {
                foreach ($terms as $term_data) {
                    $subjects_count += count($term_data['subjects']);
                }
            }
        }
        $total_terms = 0;
        foreach ($grouped_resultsByYear as $terms) {
            $total_terms += count($terms);
        }
        $is_highschool = strpos($student['class_level'], 'Form') !== false || $student['class_level'] === 'Graduated';
        $expected_subjects = $is_highschool ? 44 : 30;
        $expected_terms = 8;
        $expected_credits = $is_highschool ? 132 : 100;
        ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-emerald-50 text-emerald-600 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-500">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Efficiency</span>
                </div>
                <dt class="text-sm font-bold text-slate-500">Subjects Completed</dt>
                <dd class="mt-2 text-4xl font-black text-slate-900"><?php echo $subjects_count; ?></dd>
                <div class="mt-6">
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <span>Progress</span>
                        <span><?php echo min(100, round(($subjects_count / $expected_subjects) * 100)); ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo min(100, ($subjects_count / $expected_subjects) * 100); ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-blue-50 text-blue-600 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-500">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Journey</span>
                </div>
                <dt class="text-sm font-bold text-slate-500">Terms Completed</dt>
                <dd class="mt-2 text-4xl font-black text-slate-900"><?php echo $total_terms; ?></dd>
                <div class="mt-6">
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <span>Timeline</span>
                        <span><?php echo min(100, round(($total_terms / $expected_terms) * 100)); ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-blue-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo min(100, ($total_terms / $expected_terms) * 100); ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="dashboard-card group">
                <div class="flex items-center justify-between mb-6">
                    <div class="stats-icon-wrapper bg-indigo-50 text-indigo-600 group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-500">
                        <i class="fas fa-award"></i>
                    </div>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Achievement</span>
                </div>
                <dt class="text-sm font-bold text-slate-500">Total Credits</dt>
                <dd class="mt-2 text-4xl font-black text-slate-900"><?php echo $total_credits; ?></dd>
                <div class="mt-6">
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">
                        <span>Completion</span>
                        <span><?php echo min(100, round(($total_credits / $expected_credits) * 100)); ?>%</span>
                    </div>
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div class="bg-indigo-500 h-full rounded-full transition-all duration-1000" style="width: <?php echo min(100, ($total_credits / $expected_credits) * 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GPA Trend Analysis -->
        <?php if (!empty($chart_data)): ?>
        <div class="dashboard-card mb-12">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Academic Momentum</h3>
                    <p class="text-slate-500 font-medium text-sm">GPA performance trend over time</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Live GPA Index</span>
                </div>
            </div>
            <div class="h-80">
                <canvas id="gpaTrendChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <!-- Latest Milestone (Cleaned up card) -->
            <div class="dashboard-card h-full">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Latest Milestone</h3>
                    <div class="bg-blue-50 text-blue-600 p-2 rounded-xl">
                        <i class="fas fa-rocket"></i>
                    </div>
                </div>
                <div class="space-y-4">
                    <?php if ($recent_results->num_rows > 0): ?>
                        <?php while($result = $recent_results->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-blue-200 hover:bg-white hover:shadow-md transition-all duration-300">
                            <div class="flex items-center gap-4 text-left">
                                <div class="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center font-black text-blue-600 shadow-sm">
                                    <?php echo $result['grade']; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm"><?php echo htmlspecialchars($result['subject_name']); ?></h4>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo $result['subject_code']; ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-slate-900"><?php echo $result['marks_obtained']; ?>%</p>
                                <p class="text-[10px] font-bold text-blue-500 uppercase">GP: <?php echo $result['grade_point']; ?></p>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <p class="text-slate-400 text-sm font-medium italic">No results yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Term-wise Performance (Dashboard-Card style) -->
            <div class="dashboard-card h-full">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Term Performance</h3>
                    <div class="bg-orange-50 text-orange-600 p-2 rounded-xl">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <div class="space-y-4">
                    <?php if (!empty($grouped_resultsByYear)): ?>
                        <?php 
                        // Flatten and take latest 4 terms for the summary
                        $terms_display = [];
                        foreach ($grouped_resultsByYear as $year => $terms) {
                            foreach ($terms as $term => $data) {
                                $terms_display[] = ['year' => $year, 'term' => $term, 'data' => $data];
                            }
                        }
                        $terms_display = array_reverse($terms_display);
                        $terms_display = array_slice($terms_display, 0, 4);
                        
                        foreach ($terms_display as $item): 
                            $year = $item['year'];
                            $term = $item['term'];
                            $data = $item['data'];
                            $t_gpa = $data['total_credits'] > 0 ? $data['total_grade_points'] / $data['total_credits'] : 0;
                        ?>
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center gap-4 text-left">
                                <div class="w-10 h-10 rounded-lg bg-white border border-slate-100 flex items-center justify-center font-black text-slate-600">
                                    T<?php echo $term; ?>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">Term <?php echo $term; ?></h4>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($year); ?></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-black text-blue-600">GPA <?php echo formatGPA($t_gpa); ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase"><?php echo count($data['subjects']); ?> Subjects</p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <p class="text-slate-400 text-sm font-medium italic">No historical data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Academic Inventory -->
        <?php if (!empty($grouped_resultsByClass)): ?>
            <div class="flex items-center justify-between mb-8 mt-16">
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">Academic Inventory</h3>
                    <p class="text-slate-500 font-medium">Detailed historical record of your education</p>
                </div>
                <a href="../admin/export/result_pdf.php?student_id=<?php echo $student_id; ?>" 
                   target="_blank"
                   class="premium-gradient-bg text-white px-8 py-3 rounded-2xl shadow-lg shadow-blue-200 text-sm font-bold transition-all hover:scale-105 active:scale-95">
                   <i class="fas fa-file-pdf mr-2"></i> Export Academic Transcript
                </a>
            </div>

            <?php foreach ($grouped_resultsByClass as $class_level => $years): ?>
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-8">
                        <span class="bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest px-4 py-1.5 rounded-lg shadow-lg shadow-blue-500/20">Level</span>
                        <h4 class="text-xl font-black text-slate-900 uppercase tracking-tight"><?php echo htmlspecialchars($class_level); ?></h4>
                        <div class="flex-1 h-px bg-slate-200/50"></div>
                    </div>

                    <div class="grid grid-cols-1 gap-8">
                        <?php foreach ($years as $year => $terms): ?>
                            <?php 
                            $year_credits = 0;
                            $year_gp = 0;
                            foreach ($terms as $term_data) {
                                $year_credits += $term_data['total_credits'];
                                $year_gp += $term_data['total_grade_points'];
                            }
                            $year_avg_gpa = $year_credits > 0 ? $year_gp / $year_credits : 0;
                            $year_passed = $year_avg_gpa > 1.5;
                            ?>
                            <div class="dashboard-card !p-0 overflow-hidden">
                                <div class="p-8 bg-slate-50/50 flex justify-between items-center border-b border-slate-100">
                                    <div>
                                        <h5 class="text-lg font-black text-slate-900">Academic Year: <?php echo htmlspecialchars($year); ?></h5>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">GPA Performance Index: <span class="text-slate-900"><?php echo formatGPA($year_avg_gpa); ?></span></p>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="status-badge-premium !px-5 !py-2 <?php echo $year_passed ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'; ?>">
                                            <?php echo $year_passed ? 'PROGRESSED' : 'RETAINED'; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-8 space-y-12">
                                    <?php foreach ($terms as $term => $data): ?>
                                        <div>
                                            <div class="flex items-center justify-between mb-6">
                                                <h6 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Term <?php echo $term; ?> Breakdown</h6>
                                            </div>
                                            <div class="overflow-hidden rounded-2xl border border-slate-100">
                                                <table class="table-modern !mb-0">
                                                    <thead>
                                                        <tr class="!bg-slate-50">
                                                            <th>Subject Information</th>
                                                            <th>Credits</th>
                                                            <th>Marks</th>
                                                            <th>Grade</th>
                                                            <th>Grade Point</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($data['subjects'] as $result): ?>
                                                        <tr class="hover:bg-slate-50/30 transition-colors">
                                                            <td class="text-left">
                                                                <div class="font-bold text-slate-900"><?php echo $result['subject_name']; ?></div>
                                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5"><?php echo $result['subject_code']; ?></div>
                                                            </td>
                                                            <td class="font-bold text-slate-600"><?php echo $result['credits']; ?></td>
                                                            <td class="font-black text-slate-900"><?php echo $result['marks_obtained']; ?>%</td>
                                                            <td>
                                                                <span class="status-badge-premium !text-[10px] border border-slate-100 <?php echo $result['grade'] === 'F' ? 'bg-rose-50 text-rose-600' : 'bg-white text-blue-600'; ?>">
                                                                    <?php echo $result['grade']; ?>
                                                                </span>
                                                            </td>
                                                            <td class="font-black text-slate-900"><?php echo $result['grade_point']; ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="bg-blue-600">
                                                            <td colspan="4" class="text-right py-4 px-8">
                                                                <span class="text-[10px] font-black text-white/70 uppercase tracking-widest">Term Performance GPA</span>
                                                            </td>
                                                            <td class="text-white font-black text-lg py-4 px-8">
                                                                <?php echo formatGPA($data['total_credits'] > 0 ? $data['total_grade_points'] / $data['total_credits'] : 0); ?>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="dashboard-card text-center py-24 mt-12 bg-transparent border-dashed border-2 border-slate-200">
                <i class="fas fa-inbox text-slate-200 text-6xl mb-6"></i>
                <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Academic Vault Empty</h3>
                <p class="text-slate-400 font-medium">Your historical records will appear here as soon as they are finalized by the administration.</p>
            </div>
        <?php endif; ?>

        <!-- Footer Actions -->
        <div class="mt-12 pt-12 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 text-slate-400">
                <i class="fas fa-shield-alt text-xl"></i>
                <p class="text-sm font-medium italic">Secure academic portal powered by Hirgal Nexus SRMIS Engine</p>
            </div>
            <form action="../includes/upload_avatar.php" method="post" enctype="multipart/form-data" class="flex items-center gap-4 bg-slate-100 p-2 rounded-2xl border border-slate-200/50">
                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-4">Profile Identity</label>
                <div class="flex items-center gap-3">
                    <input type="file" name="avatar" accept="image/*" required class="text-[10px] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-white file:text-blue-600 hover:file:bg-blue-50 cursor-pointer" />
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/10">Update</button>
                </div>
            </form>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($chart_data)): ?>
    const ctx = document.getElementById('gpaTrendChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Term GPA',
                data: <?php echo json_encode($chart_data); ?>,
                borderColor: '#3b82f6',
                borderWidth: 4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true,
                backgroundColor: gradient,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    displayColors: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 4.0,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { weight: 'bold', color: '#64748b' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: 'bold', color: '#64748b' } }
                }
            }
        }
    });
    <?php endif; ?>
});
</script>