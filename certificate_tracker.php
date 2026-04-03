<?php
/**
 * Certificate Tracker Page
 * Shows student's attendance, progress, and certificate eligibility
 */

// Start session
session_start();

// Include database connection
require_once 'php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user details
$user = get_user_by_id($user_id);
if (!$user) {
    header("Location: login.html");
    exit();
}

// Get user's active enrollments with attendance progress
$enrollments = get_user_enrollments($user_id);
$enrollment_progress = [];

// Calculate attendance progress for each enrollment
foreach ($enrollments as $enrollment) {
    $course_id = $enrollment['course_id'];
    $attendance_data = calculate_attendance_progress($user_id, $course_id);
    $enrollment['attendance_progress'] = $attendance_data;
    $enrollment_progress[] = $enrollment;
}

// Initialize variables for overall stats (using first enrollment for backward compatibility)
$attendance_records = [];
$total_classes = 0;
$present_count = 0;
$attendance_percentage = 0;

if (!empty($enrollment_progress)) {
    // Use first enrollment for detailed attendance records display
    $enrollment = $enrollment_progress[0];
    $course_id = $enrollment['course_id'];

    // Get course details
    $course = get_course_by_id($course_id);

    // Get attendance records for this course
    $stmt = $conn->prepare("
        SELECT a.attendance_date, a.status, a.check_in_time,
                s.day_of_week, s.start_time, s.end_time,
                u.name as instructor_name,
                c.name as course_name
        FROM attendance a
        JOIN schedules s ON a.schedule_id = s.schedule_id
        JOIN instructors i ON s.instructor_id = i.id
        JOIN users u ON i.user_id = u.id
        JOIN courses c ON a.course_id = c.id
        WHERE a.user_id = ? AND a.course_id = ?
        ORDER BY a.attendance_date DESC
    ");

    if ($stmt === false) {
        die("SQL Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $course_id);

    if (!$stmt->execute()) {
        die("SQL Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $attendance_records = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    // Use calculated progress data
    $total_classes = $enrollment['attendance_progress']['total_classes'];
    $present_count = $enrollment['attendance_progress']['attended_classes'];
    $attendance_percentage = $enrollment['attendance_progress']['attendance_percentage'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Tracker - TaalBeats Dance Academy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .progress-bar-custom {
            height: 25px;
            font-weight: bold;
        }
        .certificate-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .certificate-eligible {
            border-color: #28a745 !important;
            background-color: #d4edda;
        }
        .certificate-not-eligible {
            border-color: #dc3545 !important;
            background-color: #f8d7da;
        }
        .attendance-present {
            color: #28a745;
        }
        .attendance-absent {
            color: #dc3545;
        }
        .attendance-late {
            color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h2 class="mb-0"><i class="fas fa-certificate"></i> Certificate Tracker</h2>
                        <a href="profile.php" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Profile
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Student Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4>Student Details</h4>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <?php if (!empty($enrollments)): ?>
                                    <p><strong>Enrollment ID:</strong> <?php echo htmlspecialchars($enrollment['id']); ?></p>
                                    <p><strong>Course:</strong> <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                                    <p><strong>Enrollment Date:</strong> <?php echo htmlspecialchars($enrollment['enrollment_date']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h4>Attendance Summary</h4>
                                <div class="mb-3">
                                    <label class="form-label">Attendance Progress</label>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-custom <?php echo $attendance_percentage >= 80 ? 'bg-success' : 'bg-warning'; ?>"
                                             role="progressbar"
                                             style="width: <?php echo $attendance_percentage; ?>%"
                                             aria-valuenow="<?php echo $attendance_percentage; ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                            <?php echo $attendance_percentage; ?>%
                                        </div>
                                    </div>
                                </div>
                                <p><strong>Total Classes:</strong> <?php echo $total_classes; ?></p>
                                <p><strong>Present:</strong> <span class="text-success"><?php echo $present_count; ?></span></p>
                                <p><strong>Absent:</strong> <span class="text-danger"><?php echo $total_classes - $present_count; ?></span></p>
                            </div>
                        </div>

                        <!-- All Courses Attendance Progress -->
                        <?php if (!empty($enrollment_progress)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4><i class="fas fa-chart-line me-2"></i>All Courses Progress</h4>
                                <div class="row">
                                    <?php foreach ($enrollment_progress as $enrollment): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($enrollment['course_name']); ?></h6>
                                                        <span class="badge <?php
                                                            echo $enrollment['status'] === 'completed' ? 'bg-success' :
                                                                 ($enrollment['status'] === 'active' ? 'bg-info' : 'bg-secondary');
                                                        ?>">
                                                            <?php echo ucfirst($enrollment['status']); ?>
                                                        </span>
                                                    </div>

                                                    <p class="card-text text-muted small mb-2">
                                                        <i class="fas fa-dance me-1"></i><?php echo htmlspecialchars($enrollment['dance_style']); ?> |
                                                        <i class="fas fa-signal me-1"></i><?php echo htmlspecialchars($enrollment['level']); ?>
                                                    </p>

                                                    <div class="mb-2">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <small class="text-muted">Attendance Progress</small>
                                                            <small class="fw-bold">
                                                                <?php echo $enrollment['attendance_progress']['attended_classes']; ?>/<?php echo $enrollment['attendance_progress']['total_classes']; ?>
                                                                (<?php echo $enrollment['attendance_progress']['attendance_percentage']; ?>%)
                                                            </small>
                                                        </div>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar <?php
                                                                echo $enrollment['attendance_progress']['attendance_percentage'] >= 80 ? 'bg-success' :
                                                                     ($enrollment['attendance_progress']['attendance_percentage'] >= 60 ? 'bg-warning' : 'bg-danger');
                                                            ?>" role="progressbar"
                                                                 style="width: <?php echo $enrollment['attendance_progress']['attendance_percentage']; ?>%"
                                                                 aria-valuenow="<?php echo $enrollment['attendance_progress']['attendance_percentage']; ?>"
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-center">
                                                        <small class="text-muted">
                                                            Enrolled: <?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Certificate Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="certificate-card p-4 <?php echo $attendance_percentage >= 80 ? 'certificate-eligible' : 'certificate-not-eligible'; ?>">
                                    <h4><i class="fas fa-award"></i> Certificate Status</h4>
                                    <?php if ($attendance_percentage >= 80): ?>
                                        <div class="alert alert-success">
                                            <h5 class="alert-heading">🎉 Congratulations!</h5>
                                            <p>You are eligible for a certificate with <?php echo $attendance_percentage; ?>% attendance.</p>
                                            <button class="btn btn-success btn-lg">
                                                <i class="fas fa-download"></i> Download Certificate
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-danger">
                                            <h5 class="alert-heading">⚠️ Not Eligible</h5>
                                            <p>You need at least 80% attendance to be eligible for a certificate. Current attendance: <?php echo $attendance_percentage; ?>%</p>
                                            <p class="mb-0">Keep attending classes to improve your attendance!</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Table -->
                        <?php if (!empty($attendance_records)): ?>
                        <div class="row">
                            <div class="col-12">
                                <h4>Attendance Records</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Time</th>
                                                <th>Course</th>
                                                <th>Instructor</th>
                                                <th>Status</th>
                                                <th>Check-in Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($attendance_records as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(date('d M Y', strtotime($record['attendance_date']))); ?></td>
                                                <td><?php echo htmlspecialchars($record['day_of_week']); ?></td>
                                                <td><?php echo htmlspecialchars($record['start_time'] . ' - ' . $record['end_time']); ?></td>
                                                <td><?php echo htmlspecialchars($record['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($record['instructor_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($record['status']) {
                                                        case 'present':
                                                            $status_class = 'attendance-present';
                                                            break;
                                                        case 'absent':
                                                            $status_class = 'attendance-absent';
                                                            break;
                                                        case 'late':
                                                            $status_class = 'attendance-late';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="<?php echo $status_class; ?>">
                                                        <i class="fas fa-<?php echo $record['status'] === 'present' ? 'check-circle' : ($record['status'] === 'absent' ? 'times-circle' : 'clock'); ?>"></i>
                                                        <?php echo htmlspecialchars(ucfirst($record['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $record['check_in_time'] ? htmlspecialchars(date('H:i', strtotime($record['check_in_time']))) : '-'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h5>No Attendance Records Found</h5>
                                    <p>You haven't attended any classes yet. Start attending classes to track your progress!</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>