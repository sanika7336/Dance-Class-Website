<?php
/**
 * Get Course Data for Editing
 * Returns course information in JSON format
 */

// Start session
session_start();

// Include database connection
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get course ID from request
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

try {
    // Fetch course data
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if ($course) {
        echo json_encode([
            'success' => true,
            'course' => $course
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Course not found']);
    }
} catch (Exception $e) {
    error_log("Error fetching course: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>