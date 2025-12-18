<?php
// admin/export/result_pdf.php
// Allow both admins and students to access this export.
// Admins can download any student's transcript; students can only download their own.
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
// If Composer autoload exists, load it so optional libraries (Dompdf) are available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    // Load a small local stub so IDEs and static analysis don't error when Dompdf isn't installed
    if (file_exists(__DIR__ . '/../../stubs/dompdf.php')) {
        require_once __DIR__ . '/../../stubs/dompdf.php';
    }
}

$requested_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;

// If current user is student, they can only request their own id
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === ROLE_STUDENT) {
    $student_id = (int)$_SESSION['user_id'];
    // If an explicit student_id was provided and doesn't match the logged in student, reject
    if ($requested_student_id && $requested_student_id !== $student_id) {
        http_response_code(403);
        die('Forbidden: students may only download their own transcript');
    }
} else {
    // For admins (or other roles), allow provided id or require it
    $student_id = $requested_student_id;
}

if (!$student_id) {
    die("Student ID required");
}

$conn = getDBConnection();

// Get student information
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found");
}

// Get student results
$results = $conn->query("
    SELECT r.*, sub.subject_code, sub.subject_name, sub.credits
    FROM results r
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = $student_id
    ORDER BY r.academic_year DESC, r.term, sub.subject_code
");

// Calculate statistics
$term_gpa = [];
$total_credits = 0;
$total_grade_points = 0;

while ($result = $results->fetch_assoc()) {
    $term = $result['term'];
    $year = $result['academic_year'] ?? 'Unknown';
    $key = $year . ' - Term ' . $term;
    if (!isset($term_gpa[$key])) {
        $term_gpa[$key] = [
            'total_credits' => 0,
            'total_grade_points' => 0,
            'subjects' => []
        ];
    }
    
    $term_gpa[$key]['total_credits'] += $result['credits'];
    $term_gpa[$key]['total_grade_points'] += ($result['grade_point'] * $result['credits']);
    $term_gpa[$key]['subjects'][] = $result;
    
    $total_credits += $result['credits'];
    $total_grade_points += ($result['grade_point'] * $result['credits']);
}

// Calculate CGPA
$cgpa = $total_credits > 0 ? $total_grade_points / $total_credits : 0;

$conn->close();

// Generate HTML for PDF
function generateTranscriptHTML($student, $term_gpa, $cgpa, $total_credits) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Academic Transcript - ' . htmlspecialchars($student['name']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
            .header h1 { margin: 0; font-size: 24px; color: #1e3a8a; }
            .header h2 { margin: 5px 0; font-size: 18px; color: #666; }
            .student-info { margin-bottom: 20px; padding: 15px; background: #f8fafc; border-radius: 5px; }
            .info-row { margin: 5px 0; }
            .info-label { font-weight: bold; display: inline-block; width: 150px; }
            .semester-section { margin: 20px 0; }
            .semester-title { background: #1e3a8a; color: white; padding: 10px; font-weight: bold; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f1f5f9; font-weight: bold; }
            .grade-A { background-color: #d1fae5; }
            .grade-B { background-color: #dbeafe; }
            .grade-C { background-color: #fef3c7; }
            .grade-D { background-color: #ffedd5; }
            .grade-F { background-color: #fee2e2; }
            .summary { margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 5px; }
            .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px; }
            .grade-scale { margin-top: 20px; font-size: 12px; }
            .grade-item { display: inline-block; margin-right: 15px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>ACADEMIC TRANSCRIPT</h1>
            <h2>Student Result Management System</h2>
        </div>
        
        <div class="student-info">
            <div class="info-row"><span class="info-label">Registration No:</span> ' . htmlspecialchars($student['reg_no']) . '</div>
            <div class="info-row"><span class="info-label">Full Name:</span> ' . htmlspecialchars($student['name']) . '</div>
            <div class="info-row"><span class="info-label">Year of Study:</span> Year ' . $student['year_of_study'] . '</div>
            <div class="info-row"><span class="info-label">CGPA:</span> ' . formatGPA($cgpa) . '</div>
            <div class="info-row"><span class="info-label">Total Credits:</span> ' . $total_credits . '</div>
            <div class="info-row"><span class="info-label">Date Generated:</span> ' . date('F j, Y') . '</div>
        </div>';
    
    if (!empty($term_gpa)) {
        foreach ($term_gpa as $term_label => $data) {
            $term_gpa_value = $data['total_credits'] > 0 ? $data['total_grade_points'] / $data['total_credits'] : 0;
            
            $html .= '
            <div class="semester-section">
                <div class="semester-title">' . htmlspecialchars($term_label) . '</div>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Credits</th>
                            <th>Marks %</th>
                            <th>Grade</th>
                            <th>Grade Point</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($data['subjects'] as $result) {
                $grade_class = 'grade-' . substr($result['grade'], 0, 1);
                $html .= '
                        <tr>
                            <td>' . htmlspecialchars($result['subject_name']) . ' (' . $result['subject_code'] . ')</td>
                            <td>' . $result['credits'] . '</td>
                            <td>' . $result['marks_obtained'] . '</td>
                            <td class="' . $grade_class . '">' . $result['grade'] . '</td>
                            <td>' . $result['grade_point'] . '</td>
                        </tr>';
            }
            
            $html .= '
                        <tr style="background-color: #f1f5f9; font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Term GPA:</td>
                            <td>' . formatGPA($term_gpa_value) . '</td>
                        </tr>
                    </tbody>
                </table>
            </div>';
        }
        
        $html .= '
        <div class="summary">
            <h3>OVERALL ACADEMIC SUMMARY</h3>
            <div class="info-row"><span class="info-label">Cumulative GPA (CGPA):</span> ' . formatGPA($cgpa) . '</div>
            <div class="info-row"><span class="info-label">Total Credits Completed:</span> ' . $total_credits . '</div>
            <div class="info-row"><span class="info-label">Total Terms Completed:</span> ' . count($term_gpa) . '</div>
        </div>';
    } else {
        $html .= '<p style="text-align: center; font-style: italic; color: #666;">No academic records available.</p>';
    }
    
    // Grade scale reference
    $html .= '
        <div class="grade-scale">
            <strong>Grade Scale Reference:</strong><br>';
    
    foreach (GRADE_SCALE as $grade => $range) {
        $html .= '<span class="grade-item">' . $grade . ': ' . $range['min'] . '-' . $range['max'] . '% (GP: ' . $range['point'] . ')</span>';
    }
    
    $html .= '
        </div>
        <div class="footer">
            Generated on ' . date('F j, Y \a\t g:i A') . ' | Student Result Management System
        </div>
    </body>
    </html>';
    
    return $html;
}

// Output options
$output_type = $_GET['output'] ?? 'html'; // 'html' or 'download'
$use_pdf = isset($_GET['pdf']) && $_GET['pdf'] == '1';

$html_content = generateTranscriptHTML($student, $term_gpa, $cgpa, $total_credits);

if ($use_pdf) {
    // Try to use Dompdf if available
    if (class_exists('\Dompdf\Dompdf')) {
        // Use Dompdf to generate PDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html_content);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="transcript_' . $student['reg_no'] . '.pdf"');
        echo $pdfOutput;
        exit;
    } else {
        // Dompdf not installed; fall back to HTML and inform the user
        header('Content-Type: text/html');
        echo '<p><strong>Server-side PDF generation is not available.</strong></p>';
        echo '<p>Please either use the browser Print &rarr; Save as PDF, or install <code>dompdf/dompdf</code> via Composer.</p>';
        echo $html_content;
        exit;
    }
}

// Standard output: allow download of HTML or display in browser
if ($output_type === 'download') {
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="transcript_' . $student['reg_no'] . '.html"');
    echo $html_content;
    exit;
} else {
    echo $html_content;
}
?>