<?php
/**
 * Get Instructor Data for Editing
 * Returns instructor information in JSON format
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

// Get instructor ID from request
$instructor_id = isset($_GET['instructor_id']) ? intval($_GET['instructor_id']) : 0;

if ($instructor_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid instructor ID']);
    exit();
}

try {
    // Fetch instructor data with user information
    $stmt = $conn->prepare("
        SELECT i.*, u.name, u.email, u.phone
        FROM instructors i
        JOIN users u ON i.user_id = u.id
        WHERE i.id = ?
    ");
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();

    if ($instructor) {
        echo json_encode([
            'success' => true,
            'instructor' => $instructor
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Instructor not found']);
    }
} catch (Exception $e) {
    error_log("Error fetching instructor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>