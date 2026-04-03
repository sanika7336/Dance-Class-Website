<?php
/**
 * Schedule Handler
 * TaalBeats Dance Academy
 * Fetches and displays class schedule
 */

// Include database connection
require_once 'db.php';

// Get filter parameters
$style_filter = sanitize_input($_GET['style'] ?? '');
$instructor_filter = sanitize_input($_GET['instructor'] ?? '');

try {
    // Build the query with filters
    $query = "
        SELECT s.id, s.day_of_week as day, s.start_time as time, c.dance_style, c.level,
               s.instructor_id, s.max_capacity, s.current_bookings, s.room_number, s.is_active,
               i.name as instructor_name
        FROM schedules s
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN instructors ins ON s.instructor_id = ins.id
        LEFT JOIN users i ON ins.user_id = i.id
        WHERE s.is_active = 1
    ";
    $params = [];

    if (!empty($style_filter)) {
        $query .= " AND c.dance_style = ?";
        $params[] = $style_filter;
    }

    if (!empty($instructor_filter)) {
        $query .= " AND i.name = ?";
        $params[] = $instructor_filter;
    }

    $query .= " ORDER BY FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("SQL prepare failed: " . $conn->error);
    }

    // Bind parameters if any
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $schedule_data = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get unique styles and instructors for filters
    $styles_stmt = $conn->prepare("
        SELECT DISTINCT c.dance_style
        FROM schedules s
        LEFT JOIN courses c ON s.course_id = c.id
        WHERE s.is_active = 1
        ORDER BY c.dance_style
    ");
    if (!$styles_stmt) {
        die("SQL prepare failed: " . $conn->error);
    }
    $styles_stmt->execute();
    $styles_result = $styles_stmt->get_result();
    $styles = $styles_result->fetch_all(MYSQLI_ASSOC);
    
    $instructors_stmt = $conn->prepare("
        SELECT DISTINCT u.name
        FROM users u
        INNER JOIN instructors i ON u.id = i.user_id
        INNER JOIN schedules s ON i.id = s.instructor_id
        WHERE s.is_active = 1
        ORDER BY u.name
    ");
    if (!$instructors_stmt) {
        die("SQL prepare failed: " . $conn->error);
    }
    $instructors_stmt->execute();
    $instructors_result = $instructors_stmt->get_result();
    $instructors = $instructors_result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("Schedule Error: " . $e->getMessage());
    $schedule_data = [];
    $styles = [];
    $instructors = [];
}

// Return JSON response for AJAX requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    echo json_encode([
        'schedule' => $schedule_data,
        'styles' => array_column($styles, 'dance_style'),
        'instructors' => array_column($instructors, 'name')
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - TaalBeats Dance Academy</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-5">
        <div class="container">
            <h1 class="text-center mb-5">Class Schedule</h1>
            
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <label for="styleFilter" class="form-label">Filter by Dance Style:</label>
                    <select id="styleFilter" class="form-select">
                        <option value="">All Styles</option>
                        <?php foreach ($styles as $style): ?>
                            <option value="<?= htmlspecialchars($style['dance_style']) ?>" 
                                    <?= $style_filter === $style['dance_style'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($style['dance_style']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="instructorFilter" class="form-label">Filter by Instructor:</label>
                    <select id="instructorFilter" class="form-select">
                        <option value="">All Instructors</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <option value="<?= htmlspecialchars($instructor['name']) ?>"
                                    <?= $instructor_filter === $instructor['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($instructor['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Schedule Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Dance Style</th>
                            <th>Instructor</th>
                            <th>Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedule_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No classes found for the selected filters.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($schedule_data as $class): ?>
                                <tr>
                                    <td><?= htmlspecialchars($class['day']) ?></td>
                                    <td><?= htmlspecialchars($class['time']) ?></td>
                                    <td><?= htmlspecialchars($class['dance_style']) ?></td>
                                    <td><?= htmlspecialchars($class['instructor_name'] ?? 'TBD') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $class['level'] === 'Beginner' ? 'success' : ($class['level'] === 'Intermediate' ? 'warning' : 'danger') ?>">
                                            <?= htmlspecialchars($class['level']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">View Only</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter functionality
        document.getElementById('styleFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        document.getElementById('instructorFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        function applyFilters() {
            const style = document.getElementById('styleFilter').value;
            const instructor = document.getElementById('instructorFilter').value;
            
            const params = new URLSearchParams();
            if (style) params.append('style', style);
            if (instructor) params.append('instructor', instructor);
            
            window.location.href = 'schedule.php?' + params.toString();
        }
    </script>
</body>
</html> 