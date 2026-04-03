<?php
/**
 * Record Attendance Handler
 * Records attendance when student joins a class via Zoom/Meet
 */

session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to record attendance.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get POST data
$day = trim($_POST['day'] ?? '');
$time = trim($_POST['time'] ?? '');
$style = trim($_POST['style'] ?? '');
$instructor = trim($_POST['instructor'] ?? '');

if (empty($day) || empty($time) || empty($style)) {
    echo json_encode(['success' => false, 'message' => 'Invalid class information.']);
    exit();
}

// Get user's enrollments
$user_enrollments = get_user_enrollments($user_id);
if (empty($user_enrollments)) {
    echo json_encode(['success' => false, 'message' => 'You must enroll in a course first.']);
    exit();
}

// Map style to database style
$style_mapping = [
    'Ballet' => 'Bharatanatyam',
    'Hip Hop' => 'Hip-Hop',
    'Classical' => 'Bharatanatyam',
    'Jazz' => 'Contemporary',
    'Contemporary' => 'Contemporary',
    'Bollywood' => 'Bollywood',
    'Latin Dance' => 'Salsa',
    'Folk Dance' => 'Bharatanatyam',
    'Kathak' => 'Kathak',
    'Salsa' => 'Salsa',
    'Freestyle' => 'Hip-Hop',
    'Garba' => 'Bharatanatyam'
];

$db_style = $style_mapping[$style] ?? $style;

// Find course_id
$course_id = null;
foreach ($user_enrollments as $enrollment) {
    if ($enrollment['dance_style'] === $db_style) {
        $course_id = $enrollment['course_id'] ?? $enrollment['id'];
        break;
    }
}

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'You are not enrolled in this dance style.']);
    exit();
}

// Get schedule_id (if exists)
$schedule_id = null;
try {
    $stmt = $conn->prepare("
        SELECT schedule_id FROM schedules
        WHERE day_of_week = ? AND start_time = ? AND course_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ssi", $day, $time, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();
    $schedule_id = $schedule['schedule_id'] ?? null;
} catch (Exception $e) {
    // Schedule might not exist, continue without it
}

// Record attendance with better error handling
try {
    // Verify schedule_id exists if provided
    if ($schedule_id) {
        $verify_stmt = $conn->prepare("SELECT schedule_id FROM schedules WHERE schedule_id = ?");
        $verify_stmt->bind_param("i", $schedule_id);
        $verify_stmt->execute();
        if ($verify_stmt->get_result()->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'Error: Invalid schedule selected']);
            exit();
        }
    }

    // Check if attendance already exists for today
    $check_stmt = $conn->prepare("
        SELECT id, status FROM attendance
        WHERE user_id = ? AND course_id = ? AND attendance_date = CURDATE()
    ");
    $check_stmt->bind_param("ii", $user_id, $course_id);
    $check_stmt->execute();
    $existing_attendance = $check_stmt->get_result()->fetch_assoc();

    $attendance_status = 'present';
    $action_message = 'recorded';

    if ($existing_attendance) {
        // Update existing attendance
        $update_stmt = $conn->prepare("
            UPDATE attendance
            SET status = ?, check_in_time = NOW(), schedule_id = ?
            WHERE user_id = ? AND course_id = ? AND attendance_date = CURDATE()
        ");
        $update_stmt->bind_param("siii", $attendance_status, $schedule_id, $user_id, $course_id);

        if ($update_stmt->execute()) {
            $action_message = 'updated';
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update attendance: ' . $conn->error]);
            exit();
        }
    } else {
        // Insert new attendance record
        $stmt = $conn->prepare("
            INSERT INTO attendance (user_id, course_id, schedule_id, attendance_date, status, check_in_time)
            VALUES (?, ?, ?, CURDATE(), ?, NOW())
        ");
        $stmt->bind_param("iiis", $user_id, $course_id, $schedule_id, $attendance_status);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Failed to record attendance: ' . $conn->error]);
            exit();
        }
    }

    // Create notification
    create_notification(
        $user_id,
        'attendance',
        'Attendance ' . ucfirst($action_message),
        "Your attendance has been {$action_message} for {$style} class on {$day} at {$time}.",
        ['style' => $style, 'day' => $day, 'time' => $time, 'action' => $action_message]
    );

    echo json_encode([
        'success' => true,
        'message' => '✅ Attendance marked successfully!',
        'action' => $action_message
    ]);

} catch (Exception $e) {
    error_log("Attendance recording error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to record attendance: ' . $e->getMessage()]);
}
?>