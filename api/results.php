<?php
// api/results.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        handleGetResults($conn);
        break;
    case 'POST':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        handlePostResult($conn);
        break;
    case 'PUT':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        handlePutResult($conn);
        break;
    case 'DELETE':
        if (!isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }
        handleDeleteResult($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();

function handleGetResults($conn) {
    $id = $_GET['id'] ?? null;
    $student_id = $_GET['student_id'] ?? null;
    $term = $_GET['term'] ?? null;
    
    if ($id) {
        // Get single result
        $query = "SELECT r.*, s.reg_no, s.name as student_name, sub.subject_code, sub.subject_name 
                 FROM results r 
                 JOIN students s ON r.student_id = s.id 
                 JOIN subjects sub ON r.subject_id = sub.id 
                 WHERE r.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $result_data = $result->fetch_assoc();
        $stmt->close();
        
        if ($result_data) {
            echo json_encode(['success' => true, 'data' => $result_data]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Result not found']);
        }
    } else {
        // Get multiple results
        $query = "SELECT r.*, s.reg_no, s.name as student_name, sub.subject_code, sub.subject_name 
                 FROM results r 
                 JOIN students s ON r.student_id = s.id 
                 JOIN subjects sub ON r.subject_id = sub.id 
                 WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($student_id) {
            $query .= " AND r.student_id = ?";
            $params[] = $student_id;
            $types .= "i";
        }
        
        if ($term) {
            $query .= " AND r.term = ?";
            $params[] = $term;
            $types .= "i";
        }
        
        $query .= " ORDER BY r.term, s.reg_no";
        
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $results, 'count' => count($results)]);
    }
}

function handlePostResult($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['student_id', 'subject_id', 'marks_obtained', 'term', 'exam_date'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }
    
    $student_id = (int)$input['student_id'];
    $subject_id = (int)$input['subject_id'];
    $marks_obtained = (float)$input['marks_obtained'];
    $term = (int)$input['term'];
    $exam_date = $input['exam_date'];
    
    if (!validateMarks($marks_obtained) || !validateTerm($term)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    $grade_info = calculateGrade($marks_obtained);
    
    // Admin API: entered_by_teacher_id NULL
    $stmt = $conn->prepare("INSERT INTO results (student_id, subject_id, entered_by_teacher_id, marks_obtained, grade, grade_point, exam_date, term) VALUES (?, ?, NULL, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iidsdsi", $student_id, $subject_id, $marks_obtained, $grade_info['grade'], $grade_info['point'], $exam_date, $term);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Result created successfully',
            'data' => [
                'id' => $stmt->insert_id,
                'grade' => $grade_info['grade'],
                'grade_point' => $grade_info['point']
            ]
        ]);
    } else {
        if ($conn->errno === 1062) {
            http_response_code(409);
            echo json_encode(['error' => 'Result already exists for this student and subject in the selected semester']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create result: ' . $conn->error]);
        }
    }
    $stmt->close();
}

function handlePutResult($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Result ID required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['marks_obtained']) || !isset($input['exam_date'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $marks_obtained = (float)$input['marks_obtained'];
    $exam_date = $input['exam_date'];
    
    if (!validateMarks($marks_obtained)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid marks']);
        return;
    }
    
    $grade_info = calculateGrade($marks_obtained);
    
    $stmt = $conn->prepare("UPDATE results SET marks_obtained = ?, grade = ?, grade_point = ?, exam_date = ? WHERE id = ?");
    $stmt->bind_param("dsssi", $marks_obtained, $grade_info['grade'], $grade_info['point'], $exam_date, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Result updated successfully',
            'data' => [
                'grade' => $grade_info['grade'],
                'grade_point' => $grade_info['point']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update result: ' . $conn->error]);
    }
    $stmt->close();
}

function handleDeleteResult($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Result ID required']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM results WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Result deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete result: ' . $conn->error]);
    }
    $stmt->close();
}
?>