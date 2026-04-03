<?php
/**
 * Check Enrollment Status
 * Validates if a user is enrolled in a specific course
 */

// Start session
session_start();

// Include database connection
require_once 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['enrolled' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$course_id = trim($_POST['course_id'] ?? '');

if (empty($course_id)) {
    echo json_encode(['enrolled' => false, 'message' => 'Course ID is required']);
    exit();
}

try {
    // Check if user is enrolled in the course
    $stmt = $conn->prepare("
        SELECT e.id
        FROM enrollments e
        WHERE e.user_id = ? AND e.course_id = ? AND e.status IN ('active', 'in-progress')
    ");

    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['enrolled' => true, 'message' => 'User is enrolled in this course']);
    } else {
        echo json_encode(['enrolled' => false, 'message' => 'User is not enrolled in this course']);
    }

} catch (Exception $e) {
    error_log("Enrollment check error: " . $e->getMessage());
    echo json_encode(['enrolled' => false, 'message' => 'Error checking enrollment status']);
}
?>