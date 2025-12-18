<?php
// api/auth.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$conn = getDBConnection();

switch ($method) {
    case 'POST':
        handleLogin($conn);
        break;
    case 'GET':
        handleCheckAuth();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

$conn->close();

function handleLogin($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        return;
    }
    
    $username = sanitizeInput($input['username']);
    $password = $input['password'];
    
    // Try admin login
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (verifyPassword($password, $admin['password_hash'])) {
            session_start();
            loginUser($admin, ROLE_ADMIN);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $admin['id'],
                    'username' => $admin['username'],
                    'role' => ROLE_ADMIN,
                    'redirect' => '../admin/dashboard.php'
                ]
            ]);
            return;
        }
    }
    
    // Try student login
    $stmt = $conn->prepare("SELECT id, reg_no, name, password_hash FROM students WHERE reg_no = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
        if (verifyPassword($password, $student['password_hash'])) {
            session_start();
            loginUser($student, ROLE_STUDENT);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $student['id'],
                    'username' => $student['reg_no'],
                    'name' => $student['name'],
                    'role' => ROLE_STUDENT,
                    'redirect' => '../student/dashboard.php'
                ]
            ]);
            return;
        }
    }

    // Try teacher login
    $stmt = $conn->prepare("SELECT id, reg_no, name, password_hash FROM teachers WHERE reg_no = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $teacher = $result->fetch_assoc();
        if (verifyPassword($password, $teacher['password_hash'])) {
            session_start();
            loginUser($teacher, ROLE_TEACHER);

            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $teacher['id'],
                    'username' => $teacher['reg_no'],
                    'name' => $teacher['name'],
                    'role' => ROLE_TEACHER,
                    'redirect' => '../teacher/dashboard.php'
                ]
            ]);
            return;
        }
    }
    
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password']);
}

function handleCheckAuth() {
    session_start();
    
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'data' => [
                'user_id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role'],
                'name' => $_SESSION['name'] ?? ''
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
    }
}
?>