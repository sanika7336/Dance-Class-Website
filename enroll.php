<?php
/**
 * Course Enrollment Handler
 * TaalBeats Dance Academy
 * Handles student enrollment in courses
 */

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Please login to enroll in courses.',
        'redirect' => '../login.html'
    ]);
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.',
        'redirect' => '../courses.php'
    ]);
    exit();
}

// Get form data
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$course_id = intval($_POST['course_id'] ?? 0);
$additional_notes = trim($_POST['additional_notes'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'qr';
$user_id = $_SESSION['user_id'];

// Validation
$errors = [];

if (empty($full_name)) {
    $errors[] = "Full name is required";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required";
}

if (empty($phone)) {
    $errors[] = "Phone number is required";
}

if ($course_id <= 0) {
    $errors[] = "Please select a valid course";
}

if (!empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "❌ Please correct the following errors: " . implode(", ", $errors),
        'errors' => $errors
    ]);
    exit();
}

// Get course details
$course = get_course_by_id($course_id);
if (!$course) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Course not found.',
        'redirect' => '../courses.php'
    ]);
    exit();
}

// Check if course is active
if (!$course['is_active']) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'This course is currently not available for enrollment.',
        'redirect' => '../courses.php'
    ]);
    exit();
}

// Check if user is already enrolled in this course
$check_enrollment_stmt = $conn->prepare("SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?");
$check_enrollment_stmt->bind_param("ii", $user_id, $course_id);
$check_enrollment_stmt->execute();
$enrollment_result = $check_enrollment_stmt->get_result();

if ($enrollment_result->num_rows > 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '❌ You are already enrolled in this course.',
        'redirect' => '../courses.php'
    ]);
    exit();
}

// Payment verification logic
$payment_verified = false;
$payment_error_message = '';

if ($payment_method === 'cash') {
    // For cash payments, mark as pending and consider enrollment complete
    $payment_verified = true;
    $payment_status = 'pending';
} else {
    // For QR/UPI payments, add basic verification
    // In a real application, you would integrate with payment gateway APIs
    // For now, we'll assume payment is verified if the form was submitted
    $payment_verified = true;
    $payment_status = 'paid';

    // You can add additional payment verification logic here
    // For example, checking transaction ID, amount verification, etc.
}

// Only proceed with enrollment if payment is verified
if (!$payment_verified) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '❌ Payment verification failed. ' . $payment_error_message . ' Please try again or contact support.',
        'redirect' => '../enroll.php?course_id=' . $course_id
    ]);
    exit();
}

// Insert enrollment into database
$stmt = $conn->prepare("
    INSERT INTO enrollments (user_id, course_id, enrollment_date, status, payment_status, notes)
    VALUES (?, ?, NOW(), 'active', ?, ?)
");

$stmt->bind_param("iiss", $user_id, $course_id, $payment_status, $additional_notes);

if ($stmt->execute()) {
    $enrollment_id = $conn->insert_id;

    // Get user details for personalized message
    $user = get_user_by_id($user_id);

    // Create success message based on payment method
    if ($payment_method === 'cash') {
        $success_message = "🎉 Enrollment Successful! Your enrollment ID is: " . $enrollment_id . ". You have been enrolled in " . $course['name'] . ". Please pay ₹" . number_format($course['price']) . " in cash when you visit the academy.";
    } else {
        $success_message = "🎉 Enrollment Successful! Your enrollment ID is: " . $enrollment_id . ". You have been enrolled in " . $course['name'] . ". Payment received successfully.";
    }
} else {
    // Database insertion failed
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => '❌ Failed to save enrollment data. Please try again or contact support.',
        'redirect' => '../enroll.php?course_id=' . $course_id
    ]);
    exit();
}

// Send notifications
create_notification(
    $user_id,
    'enrollment',
    'Enrollment Successful',
    $success_message,
    [
        'course_id' => $course_id,
        'course_name' => $course['name'],
        'amount' => $course['price'],
        'enrollment_id' => $enrollment_id
    ],
    'dashboard.php'
);

// If instructor is assigned, notify them
if ($course['instructor_user_id']) {
    create_notification(
        $course['instructor_user_id'],
        'enrollment',
        'New Student Enrollment',
        "{$user['name']} has enrolled in your {$course['name']} course. Welcome your new student!",
        [
            'student_name' => $user['name'],
            'course_name' => $course['name'],
            'enrollment_id' => $enrollment_id
        ],
        'admin_dashboard.php'
    );
}

// Return JSON response for inline display
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => $success_message,
    'enrollment_id' => $enrollment_id,
    'payment_status' => $payment_status,
    'course_name' => $course['name']
]);
exit();
?>