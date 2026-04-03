<?php
/**
 * Class Booking Handler for Join Now links
 * TaalBeats Dance Academy
 * Handles direct booking from schedule table links
 */

session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html?status=error&message=" . urlencode("Please login to book a class."));
    exit();
}

$user_id = $_SESSION['user_id'];

// Get parameters from URL
$day = trim($_GET['day'] ?? '');
$time = trim($_GET['time'] ?? '');
$style = trim($_GET['style'] ?? '');
$instructor = trim($_GET['instructor'] ?? '');
$schedule_id = intval($_GET['schedule_id'] ?? 0);

// Validate parameters
if (empty($schedule_id) || empty($day) || empty($time) || empty($style)) {
    $_SESSION['booking_message'] = "Invalid class information.";
    $_SESSION['booking_type'] = "error";
    header("Location: ../schedule.php");
    exit();
}

// Get schedule details to verify and get course_id
$stmt = $conn->prepare("SELECT s.*, c.dance_style FROM schedules s LEFT JOIN courses c ON s.course_id = c.id WHERE s.id = ? AND s.is_active = 1");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if (!$schedule) {
    $_SESSION['booking_message'] = "Schedule not found.";
    $_SESSION['booking_type'] = "error";
    header("Location: ../schedule.php");
    exit();
}

$course_id = $schedule['course_id'];
$db_style = $schedule['dance_style'];

// Check if user is enrolled in the course
$user_enrollments = get_user_enrollments($user_id);
$enrolled_course_ids = array_column($user_enrollments, 'course_id');

if (!in_array($course_id, $enrolled_course_ids)) {
    $_SESSION['booking_message'] = "You are not enrolled in this course. Please enroll first.";
    $_SESSION['booking_type'] = "error";
    header("Location: ../schedule.php");
    exit();
}

// Calculate the next class date for the given day of week
$day_of_week = $schedule['day_of_week'];
$days_map = [
    'Monday' => 1,
    'Tuesday' => 2,
    'Wednesday' => 3,
    'Thursday' => 4,
    'Friday' => 5,
    'Saturday' => 6,
    'Sunday' => 0
];

$current_day = date('w'); // 0 = Sunday, 1 = Monday, etc.
$target_day = $days_map[$day_of_week] ?? 1;

$days_until_next = ($target_day - $current_day + 7) % 7;
if ($days_until_next == 0) {
    $days_until_next = 7; // If it's today, book for next week
}

$class_date = date('Y-m-d', strtotime("+{$days_until_next} days"));

// Check if user already has a booking for this schedule and date
$check_stmt = $conn->prepare("SELECT id FROM bookings WHERE user_id = ? AND schedule_id = ? AND class_date = ?");
$check_stmt->bind_param("iis", $user_id, $schedule_id, $class_date);
$check_stmt->execute();
$existing_booking = $check_stmt->get_result()->fetch_assoc();

if ($existing_booking) {
    $_SESSION['booking_message'] = "You already have a booking for this class on {$class_date}.";
    $_SESSION['booking_type'] = "error";
    header("Location: ../schedule.php");
    exit();
}

// Create booking
try {
    $stmt = $conn->prepare("
        INSERT INTO bookings (user_id, course_id, schedule_id, booking_date, class_date, status, notes, created_at)
        VALUES (?, ?, ?, NOW(), ?, 'confirmed', ?, NOW())
    ");
    $booking_notes = "Class: {$style}, Day: {$day}, Time: {$time}, Instructor: {$instructor}";
    $stmt->bind_param("iiiss", $user_id, $course_id, $schedule_id, $class_date, $booking_notes);

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;

        // Create notification for user
        create_notification(
            $user_id,
            'booking',
            'Class Booked Successfully',
            "You have successfully booked a {$style} class for {$day} at {$time} on {$class_date}.",
            ['booking_id' => $booking_id, 'style' => $style, 'day' => $day, 'time' => $time, 'date' => $class_date]
        );

        // Create notification for admin about new booking
        create_notification(
            1, // Assuming admin user ID is 1
            'booking',
            'New Class Booking',
            "New booking: {$style} class on {$class_date} at {$time} by user ID {$user_id}",
            ['booking_id' => $booking_id, 'user_id' => $user_id, 'schedule_id' => $schedule_id]
        );

        $_SESSION['booking_message'] = "Class booked successfully for {$class_date}!";
        $_SESSION['booking_type'] = "success";
    } else {
        $_SESSION['booking_message'] = "Booking failed. Please try again.";
        $_SESSION['booking_type'] = "error";
    }
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    $_SESSION['booking_message'] = "Booking failed. Please try again.";
    $_SESSION['booking_type'] = "error";
}

header("Location: ../schedule.php");
exit();
?>