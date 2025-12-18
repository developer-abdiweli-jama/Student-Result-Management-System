<?php
// api/students.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'GET':
        handleGetStudents($conn);
        break;
    case 'POST':
        handlePostStudent($conn);
        break;
    case 'PUT':
        handlePutStudent($conn);
        break;
    case 'DELETE':
        handleDeleteStudent($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();

function handleGetStudents($conn) {
    $id = $_GET['id'] ?? null;
    $search = $_GET['search'] ?? '';
    $year = $_GET['year'] ?? '';
    
    if ($id) {
        // Get single student
        $stmt = $conn->prepare("SELECT id, reg_no, name, year_of_study, created_at FROM students WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
        
        if ($student) {
            echo json_encode(['success' => true, 'data' => $student]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
        }
    } else {
        // Get multiple students
        $query = "SELECT id, reg_no, name, year_of_study, created_at FROM students WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($search)) {
            $query .= " AND (name LIKE ? OR reg_no LIKE ?)";
            $search_term = "%$search%";
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= "ss";
        }
        
        if (!empty($year)) {
            $query .= " AND year_of_study = ?";
            $params[] = $year;
            $types .= "i";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        echo json_encode(['success' => true, 'data' => $students, 'count' => count($students)]);
    }
}

function handlePostStudent($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['year_of_study']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $name = sanitizeInput($input['name']);
    $year_of_study = (int)$input['year_of_study'];
    $password = $input['password'];
    
    if (!validatePassword($password) || !validateYear($year_of_study)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        return;
    }
    
    $reg_no = generateRegNo($year_of_study);
    $password_hash = hashPassword($password);
    
    $stmt = $conn->prepare("INSERT INTO students (reg_no, name, password_hash, year_of_study) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $reg_no, $name, $password_hash, $year_of_study);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Student created successfully',
            'data' => [
                'id' => $stmt->insert_id,
                'reg_no' => $reg_no,
                'name' => $name,
                'year_of_study' => $year_of_study
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create student: ' . $conn->error]);
    }
    $stmt->close();
}

function handlePutStudent($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID required']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['name']) || !isset($input['year_of_study'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $name = sanitizeInput($input['name']);
    $year_of_study = (int)$input['year_of_study'];
    $password = $input['password'] ?? '';
    
    if (!validateYear($year_of_study)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid year of study']);
        return;
    }
    
    if (!empty($password) && !validatePassword($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid password']);
        return;
    }
    
    if (!empty($password)) {
        $password_hash = hashPassword($password);
        $stmt = $conn->prepare("UPDATE students SET name = ?, year_of_study = ?, password_hash = ? WHERE id = ?");
        $stmt->bind_param("sisi", $name, $year_of_study, $password_hash, $id);
    } else {
        $stmt = $conn->prepare("UPDATE students SET name = ?, year_of_study = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $year_of_study, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Student updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update student: ' . $conn->error]);
    }
    $stmt->close();
}

function handleDeleteStudent($conn) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID required']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Student deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete student: ' . $conn->error]);
    }
    $stmt->close();
}
?>