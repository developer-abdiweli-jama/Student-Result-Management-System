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

// Top performing students
$topStudents = $conn->query("
    SELECT s.reg_no, s.name, s.year_of_study,
           AVG(r.grade_point) as avg_gpa,
           COUNT(r.id) as subjects_count
    FROM students s
    JOIN results r ON s.id = r.student_id
    GROUP BY s.id, s.reg_no, s.name, s.year_of_study
    HAVING COUNT(r.id) >= 3
    ORDER BY avg_gpa DESC
    LIMIT 10
");

$page_title = "Reports & Analytics";
$page_scripts = ['admin/reports.js'];
include '../includes/header.php';
include '../includes/admin_sidebar.php';
?>

<div class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="text-gray-600">Comprehensive analysis of academic performance and statistics</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Students</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalStudents; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Results Published</h3>
                    <p class="text-2xl font-bold text-gray-900"><?php echo $totalResults; ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Average GPA</h3>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php 
                        $avgGPA = $conn->query("SELECT AVG(grade_point) as avg_gpa FROM results")->fetch_assoc()['avg_gpa'] ?? 0;
                        echo formatGPA($avgGPA);
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Failure Rate</h3>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php
                        $failRate = $conn->query("SELECT ROUND((SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as rate FROM results")->fetch_assoc()['rate'] ?? 0;
                        echo $failRate . '%';
                        ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-md mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Term-wise Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Results</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Average GPA</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($term = $termStats->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            Term <?php echo $term['term']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $term['total_results']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo formatGPA($term['avg_marks']); ?>%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $term['avg_gpa'] >= 3.0 ? 'bg-green-100 text-green-800' : 
                                       ($term['avg_gpa'] >= 2.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo formatGPA($term['avg_gpa']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Subject-wise Performance -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Subject-wise Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg GPA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failures</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php while($subject = $subjectStats->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $subject['subject_name']; ?></div>
                            <div class="text-sm text-gray-500"><?php echo $subject['subject_code']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $subject['class_level']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $subject['total_students']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $subject['avg_marks'] ? formatGPA($subject['avg_marks']) . '%' : 'N/A'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($subject['avg_gpa']): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $subject['avg_gpa'] >= 3.0 ? 'bg-green-100 text-green-800' : 
                                       ($subject['avg_gpa'] >= 2.0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                <?php echo formatGPA($subject['avg_gpa']); ?>
                            </span>
                            <?php else: ?>
                            <span class="text-sm text-gray-500">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo $subject['failed_count']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php
// close connection if still open
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>