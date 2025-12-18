<?php
// student/select_stream.php
require_once '../includes/middleware/student_auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stream'])) {
    $stream = $_POST['stream'];
    $student_id = $_SESSION['user_id'];

    if (in_array($stream, ['General', 'Science', 'Arts'])) {
        $db = getDBConnection();
        $stmt = $db->prepare("UPDATE students SET stream = ? WHERE id = ? AND stream IS NULL AND (class_level LIKE 'Form 3%' OR class_level LIKE 'Form 4%')");
        $stmt->bind_param("si", $stream, $student_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Stream selected successfully!";
        } else {
            $_SESSION['error_msg'] = "Failed to select stream.";
        }
        $stmt->close();
        $db->close();
    }
}

header("Location: dashboard.php");
exit;
