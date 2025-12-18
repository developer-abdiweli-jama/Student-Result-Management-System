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

$conn->close();

$page_title = "Student Dashboard";
$page_scripts = ['student/dashboard.js'];
include '../includes/header.php';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Student Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Welcome, <?php echo $student['name']; ?></h1>
                        <p class="text-gray-600">
                            <?php echo $student['reg_no']; ?> • <?php echo $student['class_level']; ?>
                            <?php if ($student['stream']): ?>
                                • <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"><?php echo $student['stream']; ?> Stream</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Current CGPA</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo formatGPA($cgpa); ?></p>
                </div>
            </div>
            
            <?php 
            // Stream Selection for eligible students if not yet selected
            if ($can_choose_stream && empty($student['stream'])): 
            ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Select Your Academic Stream</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <?php if ($student['class_level'] === 'Form 2'): ?>
                                <p>Congratulations on passing Form 2! Please choose your academic track for Form 3.</p>
                            <?php else: ?>
                                <p>Please choose your academic track to see relevant subjects and assignments.</p>
                            <?php endif; ?>
                        </div>
                        <form action="select_stream.php" method="POST" class="mt-4 flex flex-wrap gap-4">
                            <button type="submit" name="stream" value="General" class="bg-white border border-blue-300 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-100 transition duration-150">General</button>
                            <button type="submit" name="stream" value="Science" class="bg-white border border-blue-300 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-100 transition duration-150">Science</button>
                            <button type="submit" name="stream" value="Arts" class="bg-white border border-blue-300 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-100 transition duration-150">Arts</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Avatar upload form -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 flex items-center justify-between">
        <form action="../includes/upload_avatar.php" method="post" enctype="multipart/form-data" class="flex items-center space-x-3">
            <label class="text-sm text-gray-700">Change profile picture:</label>
            <input type="file" name="avatar" accept="image/*" required />
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm">Upload</button>
        </form>
        
        <?php if (isset($_SESSION['success_msg'])): ?>
            <span class="text-green-600 text-sm font-medium"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></span>
        <?php endif; ?>
    </div>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Statistics Cards with Progress Charts -->
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
                // Expected maximums for progress calculations
                $is_highschool = strpos($student['class_level'], 'Form') !== false || $student['class_level'] === 'Graduated';
                $expected_subjects = $is_highschool ? 44 : 30; // 11 sub x 4 yrs vs 7.5 sub x 4 yrs
                $expected_terms = 8;    // 2 terms x 4 years
                $expected_credits = $is_highschool ? 132 : 100; // ~33 per year vs ~25 per year
                ?>
                
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Subjects Completed</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $subjects_count; ?></dd>
                                <dd class="text-xs text-gray-400">of ~<?php echo $expected_subjects; ?> expected</dd>
                            </div>
                            <div class="relative w-16 h-16">
                                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#e5e7eb" stroke-width="3"></circle>
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#10b981" stroke-width="3" 
                                        stroke-dasharray="<?php echo min(100, ($subjects_count / $expected_subjects) * 100); ?>, 100"
                                        stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-medium text-green-600"><?php echo min(100, round(($subjects_count / $expected_subjects) * 100)); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Terms Completed</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $total_terms; ?></dd>
                                <dd class="text-xs text-gray-400">of <?php echo $expected_terms; ?> total</dd>
                            </div>
                            <div class="relative w-16 h-16">
                                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#e5e7eb" stroke-width="3"></circle>
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#3b82f6" stroke-width="3" 
                                        stroke-dasharray="<?php echo min(100, ($total_terms / $expected_terms) * 100); ?>, 100"
                                        stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600"><?php echo min(100, round(($total_terms / $expected_terms) * 100)); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Credits</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900"><?php echo $total_credits; ?></dd>
                                <dd class="text-xs text-gray-400">of ~<?php echo $expected_credits; ?> expected</dd>
                            </div>
                            <div class="relative w-16 h-16">
                                <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#e5e7eb" stroke-width="3"></circle>
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="#8b5cf6" stroke-width="3" 
                                        stroke-dasharray="<?php echo min(100, ($total_credits / $expected_credits) * 100); ?>, 100"
                                        stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-sm font-medium text-purple-600"><?php echo min(100, round(($total_credits / $expected_credits) * 100)); ?>%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Results -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Results</h3>
                    </div>
                    <div class="overflow-hidden">
                        <?php if ($recent_results->num_rows > 0): ?>
                        <ul class="divide-y divide-gray-200">
                            <?php while($result = $recent_results->fetch_assoc()): ?>
                            <li class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600"><?php echo $result['grade']; ?></span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $result['subject_name']; ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $result['subject_code']; ?> • Term <?php echo $result['term'] ?? ''; ?></div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $result['marks_obtained']; ?>%</div>
                                        <div class="text-sm text-gray-500">GP: <?php echo $result['grade_point']; ?></div>
                                    </div>
                                </div>
                            </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No results available</h3>
                            <p class="mt-1 text-sm text-gray-500">Your results will appear here once they are published.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Term-wise Performance -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Term Performance</h3>
                    </div>
                    <div class="overflow-hidden">
                        <?php if (!empty($grouped_resultsByYear)): ?>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($grouped_resultsByYear as $year => $terms): ?>
                                <li class="bg-gray-50 px-6 py-2 text-xs font-bold text-gray-500 uppercase tracking-wider">
                                    Academic Year: <?php echo htmlspecialchars($year); ?>
                                </li>
                                <?php foreach ($terms as $term => $data): ?>
                                <?php 
                                $gpa = $data['total_credits'] > 0 ? $data['total_grade_points'] / $data['total_credits'] : 0;
                                $gpa_color = $gpa >= 3.0 ? 'text-green-600' : ($gpa >= 2.0 ? 'text-yellow-600' : 'text-red-600');
                                ?>
                                <li class="px-6 py-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-600">T<?php echo $term; ?></span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">Term <?php echo $term; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo count($data['subjects']); ?> subjects • <?php echo $data['total_credits']; ?> credits</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-medium <?php echo $gpa_color; ?>">GPA: <?php echo formatGPA($gpa); ?></div>
                                            <div class="text-sm text-gray-500">Completed</div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No term data</h3>
                            <p class="mt-1 text-sm text-gray-500">Your term performance will appear here once you have results.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detailed Results Table -->
            <div class="mt-8 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Detailed Academic Record</h3>
                    <a href="../admin/export/result_pdf.php?student_id=<?php echo $student_id; ?>" 
                       target="_blank"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                        Download PDF
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <?php if (!empty($grouped_resultsByClass)): ?>
                    <?php foreach ($grouped_resultsByClass as $class_level => $years): ?>
                        <div class="bg-blue-600 px-6 py-2 text-white font-bold uppercase tracking-wider">
                            Class Level: <?php echo htmlspecialchars($class_level); ?>
                        </div>
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
                        <div class="px-6 py-4 bg-gray-100 border-b border-gray-200 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Academic Year: <?php echo htmlspecialchars($year); ?></h3>
                                <p class="text-sm text-gray-600">Yearly Average GPA: <span class="font-semibold"><?php echo formatGPA($year_avg_gpa); ?></span></p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-bold <?php echo $year_passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $year_passed ? 'PASSED' : 'RETAINED'; ?>
                            </span>
                        </div>
                        <?php foreach ($terms as $term => $data): ?>
                        <div class="border-b border-gray-200 last:border-b-0">
                            <div class="px-6 py-3 bg-gray-50">
                                <h4 class="text-md font-semibold text-gray-900">Term <?php echo $term; ?></h4>
                            </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credits</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marks</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade Point</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($data['subjects'] as $result): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo $result['subject_name']; ?></div>
                                            <div class="text-sm text-gray-500"><?php echo $result['subject_code']; ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $result['credits']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $result['marks_obtained']; ?>%
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
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="2">
                                            Term GPA
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" colspan="3">
                                            <?php 
                                            $semester_gpa_value = $data['total_credits'] > 0 ? $data['total_grade_points'] / $data['total_credits'] : 0;
                                            echo formatGPA($semester_gpa_value);
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No academic records</h3>
                        <p class="mt-1 text-sm text-gray-500">Your detailed academic record will appear here once results are published.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>