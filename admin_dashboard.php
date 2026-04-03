<?php
/**
 * Admin Dashboard
 * User management panel for administrators
 */

// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: admin_login.html?status=error&message=" . urlencode("❌ Access denied. Admin privileges required."));
    exit();
}

// Include database connection
require_once 'php/db.php';

// Add profile_image column to instructors table if it doesn't exist
try {
    $conn->query("ALTER TABLE instructors ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255)");
} catch (Exception $e) {
    // Column might already exist, continue
}

// Add meeting_link column to schedules table if it doesn't exist
try {
    $conn->query("ALTER TABLE schedules ADD COLUMN IF NOT EXISTS meeting_link VARCHAR(500)");
} catch (Exception $e) {
    // Column might already exist, continue
}

// Handle user actions (delete, promote/demote)
$message = '';
$message_type = '';

// Handle schedule management actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_schedule') {
        $course_name = trim($_POST['course_name'] ?? '');
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $max_capacity = intval($_POST['max_capacity'] ?? 20);
        $room_number = trim($_POST['room_number'] ?? '');

        // Find or create course
        $course_id = 0;
        if (!empty($course_name)) {
            $stmt = $conn->prepare("SELECT id FROM courses WHERE name = ? AND is_active = 1");
            $stmt->bind_param("s", $course_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $course = $result->fetch_assoc();
                $course_id = $course['id'];
            } else {
                // Create new course
                $stmt = $conn->prepare("INSERT INTO courses (name, is_active) VALUES (?, 1)");
                $stmt->bind_param("s", $course_name);
                $stmt->execute();
                $course_id = $conn->insert_id;
            }
        }

        // Find or create instructor
        $instructor_id = 0;
        if (!empty($instructor_name)) {
            $stmt = $conn->prepare("SELECT i.id FROM instructors i JOIN users u ON i.user_id = u.id WHERE u.name = ?");
            $stmt->bind_param("s", $instructor_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $instructor = $result->fetch_assoc();
                $instructor_id = $instructor['id'];
            } else {
                // Create new user
                $email = strtolower(str_replace(' ', '.', $instructor_name)) . '@temp.com';
                $password = password_hash('temp123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("sss", $instructor_name, $email, $password);
                $stmt->execute();
                $user_id = $conn->insert_id;
                // Create instructor
                $stmt = $conn->prepare("INSERT INTO instructors (user_id, specialization) VALUES (?, 'General')");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $instructor_id = $conn->insert_id;
            }
        }

        if (!empty($course_id) && !empty($instructor_id) && !empty($day_of_week) && !empty($start_time) && !empty($end_time)) {
            // Calculate duration in minutes
            $start_timestamp = strtotime($start_time);
            $end_timestamp = strtotime($end_time);
            $duration_minutes = ($end_timestamp - $start_timestamp) / 60;

            if ($duration_minutes > 0) {
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO schedules (course_id, instructor_id, day_of_week, start_time, end_time, duration_minutes, max_capacity, room_number, meeting_link, is_active)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $stmt->bind_param("iisssisss", $course_id, $instructor_id, $day_of_week, $start_time, $end_time, $duration_minutes, $max_capacity, $room_number, $meeting_link);
                    $stmt->execute();
                    $message = "✅ Schedule added successfully!";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "❌ Error adding schedule: " . $e->getMessage();
                    $message_type = "error";
                }
            } else {
                $message = "❌ End time must be after start time.";
                $message_type = "error";
            }
        } else {
            $message = "❌ All required fields must be filled.";
            $message_type = "error";
        }
    }

    elseif ($action === 'update_schedule') {
        $schedule_id = intval($_POST['schedule_id'] ?? 0);
        $course_name = trim($_POST['course_name'] ?? '');
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $day_of_week = $_POST['day_of_week'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $max_capacity = intval($_POST['max_capacity'] ?? 20);
        $room_number = trim($_POST['room_number'] ?? '');
        $meeting_link = trim($_POST['meeting_link'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Find or create course
        $course_id = 0;
        if (!empty($course_name)) {
            $stmt = $conn->prepare("SELECT id FROM courses WHERE name = ? AND is_active = 1");
            $stmt->bind_param("s", $course_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $course = $result->fetch_assoc();
                $course_id = $course['id'];
            } else {
                // Create new course
                $stmt = $conn->prepare("INSERT INTO courses (name, is_active) VALUES (?, 1)");
                $stmt->bind_param("s", $course_name);
                $stmt->execute();
                $course_id = $conn->insert_id;
            }
        }

        // Find or create instructor
        $instructor_id = 0;
        if (!empty($instructor_name)) {
            $stmt = $conn->prepare("SELECT i.id FROM instructors i JOIN users u ON i.user_id = u.id WHERE u.name = ?");
            $stmt->bind_param("s", $instructor_name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $instructor = $result->fetch_assoc();
                $instructor_id = $instructor['id'];
            } else {
                // Create new user
                $email = strtolower(str_replace(' ', '.', $instructor_name)) . '@temp.com';
                $password = password_hash('temp123', PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("sss", $instructor_name, $email, $password);
                $stmt->execute();
                $user_id = $conn->insert_id;
                // Create instructor
                $stmt = $conn->prepare("INSERT INTO instructors (user_id, specialization) VALUES (?, 'General')");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $instructor_id = $conn->insert_id;
            }
        }

        if ($schedule_id > 0 && !empty($course_id) && !empty($instructor_id) && !empty($day_of_week) && !empty($start_time) && !empty($end_time)) {
            // Calculate duration in minutes
            $start_timestamp = strtotime($start_time);
            $end_timestamp = strtotime($end_time);
            $duration_minutes = ($end_timestamp - $start_timestamp) / 60;

            if ($duration_minutes > 0) {
                try {
                    $stmt = $conn->prepare("
                        UPDATE schedules SET course_id = ?, instructor_id = ?, day_of_week = ?, start_time = ?, end_time = ?,
                        duration_minutes = ?, max_capacity = ?, room_number = ?, meeting_link = ?, is_active = ? WHERE id = ?
                    ");
                    $stmt->bind_param("iisssisssii", $course_id, $instructor_id, $day_of_week, $start_time, $end_time, $duration_minutes, $max_capacity, $room_number, $meeting_link, $is_active, $schedule_id);
                    $stmt->execute();
                    $message = "✅ Schedule updated successfully!";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "❌ Error updating schedule: " . $e->getMessage();
                    $message_type = "error";
                }
            } else {
                $message = "❌ End time must be after start time.";
                $message_type = "error";
            }
        } else {
            $message = "❌ All required fields must be filled.";
            $message_type = "error";
        }
    }

    elseif ($action === 'delete_schedule') {
        $schedule_id = intval($_POST['schedule_id'] ?? 0);

        if ($schedule_id > 0) {
            try {
                $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
                $stmt->bind_param("i", $schedule_id);
                $stmt->execute();
                $message = "✅ Schedule deleted successfully!";
                $message_type = "success";
            } catch (Exception $e) {
                $message = "❌ Error deleting schedule: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Invalid schedule ID.";
            $message_type = "error";
        }
    }

    // Handle course management actions
    
    if ($action === 'add_course') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration_weeks = intval($_POST['duration_weeks'] ?? 12);
        $duration_months = floatval($_POST['duration_months'] ?? 3.0);
        $fees = floatval($_POST['fees'] ?? 0);
        $schedule = trim($_POST['schedule'] ?? '');
        $level = $_POST['level'] ?? 'Beginner';
        $category = $_POST['category'] ?? 'classical';
        $max_students = intval($_POST['max_students'] ?? 20);
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $image_url = trim($_POST['image_url'] ?? '');
        
        if (!empty($title) && $fees > 0) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO courses (name, description, duration_weeks, price, level, dance_style, max_students, instructor_id, image_url, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, 1)
                ");
                $stmt->bind_param("ssidsiss", $title, $description, $duration_weeks, $fees, $level, $category, $max_students, $image_url);
                if ($stmt->execute()) {
                    $message = "✅ Course added successfully!";
                    $message_type = "success";
                } else {
                    $message = "❌ Error adding course: " . $conn->error;
                    $message_type = "error";
                }
            } catch (Exception $e) {
                $message = "❌ Error adding course: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Course title and fees are required.";
            $message_type = "error";
        }
    }
    
    elseif ($action === 'update_enrollment') {
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);
        $payment_status = $_POST['payment_status'] ?? '';
        $course_status = $_POST['course_status'] ?? '';
        $progress_percentage = floatval($_POST['progress_percentage'] ?? 0);

        if ($enrollment_id > 0) {
            try {
                $update_fields = [];
                $params = [];
                $types = "";

                if (!empty($payment_status)) {
                    $update_fields[] = "payment_status = ?";
                    $params[] = $payment_status;
                    $types .= "s";
                }
                if (!empty($course_status)) {
                    $update_fields[] = "status = ?";
                    $params[] = $course_status;
                    $types .= "s";

                    if ($course_status === 'completed') {
                        $update_fields[] = "completion_date = NOW()";
                        $update_fields[] = "progress_percentage = 100";
                        $params[] = 100;
                        $types .= "d";
                    }
                }
                if ($progress_percentage >= 0) {
                    $update_fields[] = "progress_percentage = ?";
                    $params[] = $progress_percentage;
                    $types .= "d";
                }

                if (!empty($update_fields)) {
                    $params[] = $enrollment_id;
                    $types .= "i";
                    $sql = "UPDATE enrollments SET " . implode(', ', $update_fields) . " WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();

                    $message = "✅ Enrollment updated successfully!";
                    $message_type = "success";
                }
            } catch (Exception $e) {
                $message = "❌ Error updating enrollment: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }
    
    elseif ($action === 'generate_certificate') {
        $enrollment_id = intval($_POST['enrollment_id'] ?? 0);

        if ($enrollment_id > 0) {
            try {
                // Get enrollment details
                $stmt = $conn->prepare("
                    SELECT e.*, c.name as course_title, u.name as student_name
                    FROM enrollments e
                    JOIN courses c ON e.course_id = c.id
                    JOIN users u ON e.user_id = u.id
                    WHERE e.id = ?
                ");
                $stmt->bind_param("i", $enrollment_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $enrollment = $result->fetch_assoc();

                if ($enrollment && ($enrollment['status'] === 'completed' || $enrollment['progress_percentage'] >= 100)) {
                    // Check if certificate already exists
                    $cert_check = $conn->prepare("SELECT id FROM certificates WHERE enrollment_id = ?");
                    $cert_check->bind_param("i", $enrollment_id);
                    $cert_check->execute();
                    $cert_result = $cert_check->get_result();

                    if (!$cert_result->fetch_assoc()) {
                        $certificate_number = 'TB' . date('Y') . sprintf('%06d', $enrollment_id);
                        $completion_date = $enrollment['completion_date'] ? date('Y-m-d', strtotime($enrollment['completion_date'])) : date('Y-m-d');

                        $cert_stmt = $conn->prepare("
                            INSERT INTO certificates (user_id, course_id, enrollment_id, certificate_number, completion_date)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $cert_stmt->bind_param("iiiss", $enrollment['user_id'], $enrollment['course_id'], $enrollment_id, $certificate_number, $completion_date);
                        $cert_stmt->execute();

                        $message = "✅ Certificate generated successfully!";
                        $message_type = "success";
                    } else {
                        $message = "⚠️ Certificate already exists for this enrollment.";
                        $message_type = "warning";
                    }
                } else {
                    $message = "❌ Course must be completed before generating certificate.";
                    $message_type = "error";
                }
            } catch (Exception $e) {
                $message = "❌ Error generating certificate: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }

    elseif ($action === 'add_google_meet_link') {
        $meet_link = 'https://meet.google.com/hxb-vxox-ccd';

        try {
            $stmt = $conn->prepare("
                UPDATE schedules
                SET meeting_link = ?
                WHERE is_active = 1 AND (meeting_link IS NULL OR meeting_link = '')
            ");
            $stmt->bind_param("s", $meet_link);

            if ($stmt->execute()) {
                $affected_rows = $stmt->affected_rows;
                $message = "✅ Google Meet link added successfully to {$affected_rows} schedule(s)! Link: {$meet_link}";
                $message_type = "success";
            } else {
                $message = "❌ Error updating schedules: " . $conn->error;
                $message_type = "error";
            }
        } catch (Exception $e) {
            $message = "❌ Error: " . $e->getMessage();
            $message_type = "error";
        }
    }

    elseif ($action === 'add_instructor') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $experience_years = intval($_POST['experience_years'] ?? 0);
        $bio = trim($_POST['bio'] ?? '');
        $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);

        if (!empty($name) && !empty($email)) {
            try {
                // Check if user with this email already exists
                $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $check_stmt->bind_param("s", $email);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();

                if ($check_result->num_rows > 0) {
                    $message = "❌ A user with this email already exists.";
                    $message_type = "error";
                } else {
                    // Handle profile image upload
                    $profile_image_path = null;
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'uploads/profile_pictures/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_extension, $allowed_extensions)) {
                            $new_filename = 'instructor_' . time() . '_' . uniqid() . '.' . $file_extension;
                            $upload_path = $upload_dir . $new_filename;

                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                                $profile_image_path = $upload_path;
                            } else {
                                $message = "❌ Error uploading profile image.";
                                $message_type = "error";
                                goto end_instructor_add;
                            }
                        } else {
                            $message = "❌ Invalid file type. Only JPG, PNG, and GIF files are allowed.";
                            $message_type = "error";
                            goto end_instructor_add;
                        }
                    }

                    // Create user account for instructor
                    $password = password_hash('temp123', PASSWORD_DEFAULT);
                    $user_stmt = $conn->prepare("
                        INSERT INTO users (name, email, phone, password, role, is_active, dance_style)
                        VALUES (?, ?, ?, ?, 'instructor', 1, ?)
                    ");
                    $user_stmt->bind_param("sssss", $name, $email, $phone, $password, $specialization);
                    $user_stmt->execute();
                    $user_id = $conn->insert_id;

                    // Create instructor record
                    $instructor_stmt = $conn->prepare("
                        INSERT INTO instructors (user_id, specialization, experience_years, bio, hourly_rate, profile_image)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $instructor_stmt->bind_param("isssds", $user_id, $specialization, $experience_years, $bio, $hourly_rate, $profile_image_path);
                    $instructor_stmt->execute();

                    $message = "✅ Instructor added successfully! Temporary password: temp123";
                    $message_type = "success";

                    end_instructor_add:
                }
            } catch (Exception $e) {
                $message = "❌ Error adding instructor: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Name and email are required.";
            $message_type = "error";
        }
    }

    elseif ($action === 'edit_course') {
        $course_id = intval($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration_weeks = intval($_POST['duration_weeks'] ?? 12);
        $fees = floatval($_POST['fees'] ?? 0);
        $level = $_POST['level'] ?? 'Beginner';
        $category = trim($_POST['category'] ?? 'classical');
        $max_students = intval($_POST['max_students'] ?? 20);
        $image_url = trim($_POST['image_url'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Debug logging
        error_log("Edit Course Debug - Course ID: $course_id, Title: $title, Category: '$category', Level: $level");

        if ($course_id > 0 && !empty($title) && $fees > 0) {
            try {
                // First, get the current course data for comparison
                $current_stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
                $current_stmt->bind_param("i", $course_id);
                $current_stmt->execute();
                $current_result = $current_stmt->get_result();
                $current_course = $current_result->fetch_assoc();

                if (!$current_course) {
                    $message = "❌ Course not found.";
                    $message_type = "error";
                } else {
                    // Log current vs new values
                    error_log("Current dance_style: '" . $current_course['dance_style'] . "', New category: '$category'");

                    $stmt = $conn->prepare("
                        UPDATE courses SET
                            name = ?, description = ?, duration_weeks = ?, price = ?,
                            level = ?, dance_style = ?, max_students = ?, image_url = ?, is_active = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param("ssidsissii", $title, $description, $duration_weeks, $fees, $level, $category, $max_students, $image_url, $is_active, $course_id);

                    error_log("Executing UPDATE query with category: '$category'");
                    $execute_result = $stmt->execute();

                    if ($execute_result) {
                        error_log("UPDATE query executed successfully");
                        // Verify the update was successful
                        $check_stmt = $conn->prepare("SELECT name, dance_style, level FROM courses WHERE id = ?");
                        $check_stmt->bind_param("i", $course_id);
                        $check_stmt->execute();
                        $check_result = $check_stmt->get_result();
                        $updated_course = $check_result->fetch_assoc();

                        if ($updated_course) {
                            error_log("Updated course - Name: " . $updated_course['name'] . ", dance_style: '" . $updated_course['dance_style'] . "', level: " . $updated_course['level']);

                            if ($updated_course['dance_style'] === $category) {
                                $message = "✅ Course updated successfully! Category set to: " . $category;
                                $message_type = "success";
                            } else {
                                $message = "❌ Course update failed - category not saved. Expected: '" . $category . "', Saved: '" . $updated_course['dance_style'] . "'";
                                $message_type = "error";
                                error_log("Category mismatch - Expected: '$category', Got: '" . $updated_course['dance_style'] . "'");
                            }
                        } else {
                            $message = "❌ Course update verification failed.";
                            $message_type = "error";
                        }
                    } else {
                        $message = "❌ Error updating course: " . $conn->error;
                        $message_type = "error";
                        error_log("Database error: " . $conn->error);
                    }
                }
            } catch (Exception $e) {
                $message = "❌ Error updating course: " . $e->getMessage();
                $message_type = "error";
                error_log("Exception: " . $e->getMessage());
            }
        } else {
            $message = "❌ Course title and fees are required.";
            $message_type = "error";
        }
    }

    elseif ($action === 'edit_instructor') {
        $instructor_id = intval($_POST['instructor_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $experience_years = intval($_POST['experience_years'] ?? 0);
        $bio = trim($_POST['bio'] ?? '');
        $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);

        if ($instructor_id > 0 && !empty($name) && !empty($email)) {
            try {
                // Get current instructor data
                $get_stmt = $conn->prepare("SELECT user_id, profile_image FROM instructors WHERE id = ?");
                $get_stmt->bind_param("i", $instructor_id);
                $get_stmt->execute();
                $result = $get_stmt->get_result();
                $instructor_data = $result->fetch_assoc();

                if ($instructor_data) {
                    $user_id = $instructor_data['user_id'];
                    $current_profile_image = $instructor_data['profile_image'];

                    // Handle profile image upload
                    $profile_image_path = $current_profile_image; // Keep current image by default
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = 'uploads/profile_pictures/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }

                        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                        if (in_array($file_extension, $allowed_extensions)) {
                            // Delete old profile image if it exists
                            if ($current_profile_image && file_exists($current_profile_image)) {
                                unlink($current_profile_image);
                            }

                            $new_filename = 'instructor_' . time() . '_' . uniqid() . '.' . $file_extension;
                            $upload_path = $upload_dir . $new_filename;

                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                                $profile_image_path = $upload_path;
                            } else {
                                $message = "❌ Error uploading profile image.";
                                $message_type = "error";
                                goto end_instructor_edit;
                            }
                        } else {
                            $message = "❌ Invalid file type. Only JPG, PNG, and GIF files are allowed.";
                            $message_type = "error";
                            goto end_instructor_edit;
                        }
                    }

                    // Update user table
                    $user_stmt = $conn->prepare("
                        UPDATE users SET name = ?, email = ?, phone = ?, dance_style = ?
                        WHERE id = ?
                    ");
                    $user_stmt->bind_param("ssssi", $name, $email, $phone, $specialization, $user_id);
                    $user_stmt->execute();

                    // Update instructor table
                    $instructor_stmt = $conn->prepare("
                        UPDATE instructors SET specialization = ?, experience_years = ?, bio = ?, hourly_rate = ?, profile_image = ?
                        WHERE id = ?
                    ");
                    $instructor_stmt->bind_param("sidsis", $specialization, $experience_years, $bio, $hourly_rate, $profile_image_path, $instructor_id);
                    $instructor_stmt->execute();

                    $message = "✅ Instructor updated successfully!";
                    $message_type = "success";

                    end_instructor_edit:
                } else {
                    $message = "❌ Instructor not found.";
                    $message_type = "error";
                }
            } catch (Exception $e) {
                $message = "❌ Error updating instructor: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Name and email are required.";
            $message_type = "error";
        }
    }


    elseif ($action === 'delete_course') {
        $course_id = intval($_POST['course_id'] ?? 0);

        if ($course_id > 0) {
            try {
                // Check if course has enrollments
                $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
                $check_stmt->bind_param("i", $course_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                $count = $result->fetch_assoc()['count'];

                if ($count > 0) {
                    $message = "❌ Cannot delete course with existing enrollments. Please cancel all enrollments first.";
                    $message_type = "error";
                } else {
                    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $message = "✅ Course deleted successfully!";
                    $message_type = "success";
                }
            } catch (Exception $e) {
                $message = "❌ Error deleting course: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Invalid course ID.";
            $message_type = "error";
        }
    }

    elseif ($action === 'delete_instructor') {
        $instructor_id = intval($_POST['instructor_id'] ?? 0);

        if ($instructor_id > 0) {
            try {
                // Get user_id from instructor record
                $get_user_stmt = $conn->prepare("SELECT user_id FROM instructors WHERE id = ?");
                $get_user_stmt->bind_param("i", $instructor_id);
                $get_user_stmt->execute();
                $user_result = $get_user_stmt->get_result();
                $instructor_data = $user_result->fetch_assoc();

                if ($instructor_data) {
                    $user_id = $instructor_data['user_id'];

                    // Delete instructor record first (due to foreign key constraint)
                    $delete_instructor_stmt = $conn->prepare("DELETE FROM instructors WHERE id = ?");
                    $delete_instructor_stmt->bind_param("i", $instructor_id);
                    $delete_instructor_stmt->execute();

                    // Delete user account
                    $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $delete_user_stmt->bind_param("i", $user_id);
                    $delete_user_stmt->execute();

                    $message = "✅ Instructor deleted successfully!";
                    $message_type = "success";
                } else {
                    $message = "❌ Instructor not found.";
                    $message_type = "error";
                }
            } catch (Exception $e) {
                $message = "❌ Error deleting instructor: " . $e->getMessage();
                $message_type = "error";
            }
        } else {
            $message = "❌ Invalid instructor ID.";
            $message_type = "error";
        }
    }
    
    // Original user management actions
    $user_id = intval($_POST['user_id'] ?? 0);
    
    if ($user_id > 0) {
        if ($action === 'delete') {
            // Delete user
            $delete_sql = "DELETE FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ User deleted successfully";
                $message_type = "success";
            } else {
                $message = "❌ Error deleting user: " . mysqli_error($conn);
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);

        } elseif ($action === 'toggle_admin') {
            // Toggle admin status
            $toggle_sql = "UPDATE users SET is_admin = 1 - is_admin WHERE id = ?";
            $stmt = mysqli_prepare($conn, $toggle_sql);
            mysqli_stmt_bind_param($stmt, "i", $user_id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "✅ User admin status updated successfully";
                $message_type = "success";
            } else {
                $message = "❌ Error updating user: " . mysqli_error($conn);
                $message_type = "error";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get current tab
$current_tab = $_GET['tab'] ?? 'users';

// Get schedules data with enhanced instructor information
try {
    $schedules_stmt = $conn->prepare("
        SELECT s.*, c.name as course_name, c.dance_style, u.name as instructor_name,
                i.specialization, i.experience_years, i.rating, i.profile_image
        FROM schedules s
        JOIN courses c ON s.course_id = c.id
        JOIN instructors i ON s.instructor_id = i.id
        JOIN users u ON i.user_id = u.id
        ORDER BY s.day_of_week, s.start_time
    ");
    $schedules_stmt->execute();
    $result = $schedules_stmt->get_result();
    $schedules = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $schedules = [];
}

// Get courses for dropdown
try {
    $courses_dropdown_stmt = $conn->prepare("SELECT id, name, dance_style FROM courses WHERE is_active = 1 ORDER BY name");
    $courses_dropdown_stmt->execute();
    $result = $courses_dropdown_stmt->get_result();
    $courses_dropdown = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $courses_dropdown = [];
}

// Get instructors for dropdown (matching instructors.php data)
try {
    $instructors_dropdown_stmt = $conn->prepare("
        SELECT i.id, u.name, i.specialization, u.dance_style, i.experience_years, i.rating, i.profile_image
        FROM instructors i
        JOIN users u ON i.user_id = u.id
        WHERE u.is_active = 1
        ORDER BY i.rating DESC, i.experience_years DESC, u.name
    ");
    $instructors_dropdown_stmt->execute();
    $result = $instructors_dropdown_stmt->get_result();
    $instructors_dropdown = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $instructors_dropdown = [];
}

// Handle search and sorting for users
$search = trim($_GET['search'] ?? '');
$sort_by = $_GET['sort'] ?? 'registered';
$sort_order = $_GET['order'] ?? 'DESC';

// Build query for users
$query = "SELECT id, name, email, registered, is_admin FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

// Add sorting
$valid_sort_columns = ['id', 'name', 'email', 'registered', 'is_admin'];
if (in_array($sort_by, $valid_sort_columns)) {
    $query .= " ORDER BY {$sort_by} {$sort_order}";
} else {
    $query .= " ORDER BY registered DESC";
}

// Execute query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

$users = [];
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
} else {
    $message = "❌ Error loading users: " . mysqli_error($conn);
    $message_type = "error";
}

mysqli_stmt_close($stmt);

// Get courses data
try {
    $courses_stmt = $conn->prepare("
        SELECT c.*, COUNT(e.id) as enrolled_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.status != 'cancelled'
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $courses_stmt->execute();
    $result = $courses_stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $courses = [];
}

// Get enrollments data
try {
    $enrollments_stmt = $conn->prepare("
        SELECT e.*, c.name as course_title, u.name as student_name, u.email as student_email
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        JOIN users u ON e.user_id = u.id
        ORDER BY e.enrollment_date DESC
        LIMIT 50
    ");
    $enrollments_stmt->execute();
    $result = $enrollments_stmt->get_result();
    $enrollments = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $enrollments = [];
}

// Get bookings data with schedule information
try {
    $bookings_stmt = $conn->prepare("
        SELECT b.*, c.name as course_name, c.dance_style, u.name as user_name, u.email as user_email,
               s.day_of_week, s.start_time, s.end_time, s.room_number,
               i.name as instructor_name
        FROM bookings b
        LEFT JOIN courses c ON b.course_id = c.id
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN schedules s ON b.schedule_id = s.schedule_id
        LEFT JOIN instructors ins ON s.instructor_id = ins.id
        LEFT JOIN users i ON ins.user_id = i.id
        ORDER BY b.created_at DESC
        LIMIT 50
    ");
    if (!$bookings_stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    $bookings_stmt->execute();
    $result = $bookings_stmt->get_result();
    $bookings = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}

// Get messages data
try {
    $messages_stmt = $conn->prepare("
        SELECT m.*, u.name as sender_name, u.email as sender_email
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.id
        ORDER BY m.sent_at DESC
        LIMIT 50
    ");
    $messages_stmt->execute();
    $result = $messages_stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $messages = [];
}

// Get statistics
try {
    $stats_stmt = $conn->prepare("
        SELECT
            (SELECT COUNT(*) FROM courses WHERE is_active = 1) as total_courses,
            (SELECT COUNT(*) FROM instructors) as total_instructors,
            (SELECT COUNT(*) FROM enrollments WHERE status != 'cancelled') as total_enrollments,
            (SELECT COUNT(*) FROM enrollments WHERE status = 'completed') as completed_enrollments,
            (SELECT COUNT(*) FROM certificates) as total_certificates,
            (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed') as total_bookings,
            (SELECT COUNT(*) FROM bookings WHERE status = 'confirmed' AND class_date >= CURDATE()) as upcoming_bookings
    ");
    $stats_stmt->execute();
    $result = $stats_stmt->get_result();
    $stats = $result->fetch_assoc();
} catch (Exception $e) {
    $stats = [
        'total_courses' => 0,
        'total_instructors' => 0,
        'total_enrollments' => 0,
        'completed_enrollments' => 0,
        'total_certificates' => 0,
        'total_bookings' => 0,
        'upcoming_bookings' => 0
    ];
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Registered', 'Admin']);

    foreach ($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['name'],
            $user['email'],
            $user['registered'],
            $user['is_admin'] ? 'Yes' : 'No'
        ]);
    }

    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TaalBeats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        .user-table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .admin-badge {
            background: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
        }
        .action-btn {
            margin: 0.125rem;
        }
    </style>
</head>
<body>
    

    <div class="admin-header">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p class="mb-0">Manage users, courses, and system settings</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="stats-card">
                        <h3><?php echo count($users); ?></h3>
                        <p class="mb-0">Total Users</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- URL Messages -->
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'users' ? 'active' : ''; ?>" href="?tab=users">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'courses' ? 'active' : ''; ?>" href="?tab=courses">Courses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'instructors' ? 'active' : ''; ?>" href="?tab=instructors">Instructors</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'schedules' ? 'active' : ''; ?>" href="?tab=schedules">Schedules</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'enrollments' ? 'active' : ''; ?>" href="?tab=enrollments">Enrollments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'messages' ? 'active' : ''; ?>" href="?tab=messages">Messages</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_tab === 'statistics' ? 'active' : ''; ?>" href="?tab=statistics">Statistics</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <?php if ($current_tab === 'users'): ?>
            <!-- Search and Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control me-2"
                                       placeholder="Search by name or email..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="?export=csv<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                               class="btn btn-success me-2">
                                <i class="fas fa-download"></i> Export CSV
                            </a>
                            <a href="admin_dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users"></i> User Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover user-table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?sort=id&order=<?php echo $sort_by === 'id' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                                            ID <i class="fas fa-sort<?php echo $sort_by === 'id' ? ($sort_order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=name&order=<?php echo $sort_by === 'name' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                                            Name <i class="fas fa-sort<?php echo $sort_by === 'name' ? ($sort_order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=email&order=<?php echo $sort_by === 'email' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                                            Email <i class="fas fa-sort<?php echo $sort_by === 'email' ? ($sort_order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=registered&order=<?php echo $sort_by === 'registered' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                                            Registered <i class="fas fa-sort<?php echo $sort_by === 'registered' ? ($sort_order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=is_admin&order=<?php echo $sort_by === 'is_admin' && $sort_order === 'ASC' ? 'DESC' : 'ASC'; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="text-decoration-none text-dark">
                                            Admin <i class="fas fa-sort<?php echo $sort_by === 'is_admin' ? ($sort_order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['registered']); ?></td>
                                        <td>
                                            <?php if ($user['is_admin']): ?>
                                                <span class="admin-badge">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="toggle_admin">
                                                <button type="submit" class="btn btn-sm btn-warning action-btn"
                                                        title="<?php echo $user['is_admin'] ? 'Demote from Admin' : 'Promote to Admin'; ?>">
                                                    <i class="fas fa-<?php echo $user['is_admin'] ? 'user-minus' : 'user-plus'; ?>"></i>
                                                </button>
                                            </form>

                                            <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="btn btn-sm btn-danger action-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($users)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No users found</h5>
                            <?php if (!empty($search)): ?>
                                <p class="text-muted">Try adjusting your search criteria</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'instructors'): ?>
            <!-- Add Instructor Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user-plus"></i> Add New Instructor</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_instructor">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_instructor">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instructor_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="instructor_name" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructor_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="instructor_email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructor_phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="instructor_phone" name="phone">
                                    </div>
                                    <div class="mb-3">
                                        <label for="specialization" class="form-label">Dance Specialization</label>
                                        <select class="form-control" id="specialization" name="specialization">
                                            <option value="">-- Select Specialization --</option>
                                            <option value="Bharatanatyam">Bharatanatyam</option>
                                            <option value="Kathak">Kathak</option>
                                            <option value="Hip-Hop">Hip-Hop</option>
                                            <option value="Salsa">Salsa</option>
                                            <option value="Contemporary">Contemporary</option>
                                            <option value="Ballet">Ballet</option>
                                            <option value="Odissi">Odissi</option>
                                            <option value="Folk">Folk</option>
                                            <option value="Bollywood">Bollywood</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profile_image" class="form-label">Profile Photo</label>
                                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                                        <div class="form-text">Upload a profile photo (JPG, PNG, GIF). Max file size: 5MB</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="experience_years" class="form-label">Years of Experience</label>
                                        <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" max="50">
                                    </div>
                                    <div class="mb-3">
                                        <label for="hourly_rate" class="form-label">Hourly Rate (₹)</label>
                                        <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" step="0.01" min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label for="bio" class="form-label">Bio/Description</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Brief description about the instructor..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Instructor
                            </button>
                        </form>
                        
                </div>
            </div>

            <!-- Instructors Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-users-cog"></i> Instructor Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Specialization</th>
                                    <th>Experience</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Get all instructors for display
                                try {
                                    $instructors_stmt = $conn->prepare("
                                        SELECT i.*, u.name, u.email, u.phone, u.is_active
                                        FROM instructors i
                                        JOIN users u ON i.user_id = u.id
                                        ORDER BY i.rating DESC, i.experience_years DESC
                                    ");
                                    $instructors_stmt->execute();
                                    $result = $instructors_stmt->get_result();
                                    $all_instructors = $result->fetch_all(MYSQLI_ASSOC);

                                    foreach ($all_instructors as $instructor): ?>
                                        <tr>
                                            <td><?php echo $instructor['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($instructor['profile_image'] ?? 'img/male-instructor.jpg'); ?>"
                                                         alt="<?php echo htmlspecialchars($instructor['name']); ?>"
                                                         class="rounded-circle me-2"
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php echo htmlspecialchars($instructor['name']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                                            <td><?php echo htmlspecialchars($instructor['phone'] ?: 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($instructor['specialization'] ?: 'General'); ?></td>
                                            <td><?php echo $instructor['experience_years'] ? $instructor['experience_years'] . ' years' : 'N/A'; ?></td>
                                            <td><?php echo $instructor['rating'] ? number_format($instructor['rating'], 1) : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $instructor['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $instructor['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editInstructor(<?php echo $instructor['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteInstructor(<?php echo $instructor['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach;
                                } catch (Exception $e) {
                                    echo '<tr><td colspan="10" class="text-center text-danger">Error loading instructors: ' . $e->getMessage() . '</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($all_instructors ?? [])): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No instructors found</h5>
                            <p class="text-muted">Add your first instructor using the form above</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'courses'): ?>
            <!-- Add Course Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-plus"></i> Add New Course</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_course">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Course Title *</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="fees" class="form-label">Fees (₹) *</label>
                                    <input type="number" class="form-control" id="fees" name="fees" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="schedule" class="form-label">Schedule</label>
                                    <input type="text" class="form-control" id="schedule" name="schedule" placeholder="e.g., Mon, Wed, Fri - 6:00 PM to 7:30 PM">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="duration_weeks" class="form-label">Duration (Weeks)</label>
                                    <input type="number" class="form-control" id="duration_weeks" name="duration_weeks" value="12">
                                </div>
                                <div class="mb-3">
                                    <label for="duration_months" class="form-label">Duration (Months)</label>
                                    <input type="number" class="form-control" id="duration_months" name="duration_months" step="0.1" value="3.0">
                                </div>
                                <div class="mb-3">
                                    <label for="level" class="form-label">Level</label>
                                    <select class="form-control" id="level" name="level">
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                        <option value="All Levels">All Levels</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-control" id="category" name="category">
                                        <option value="classical">Classical</option>
                                        <option value="contemporary">Contemporary</option>
                                        <option value="folk">Folk</option>
                                        <option value="bollywood">Bollywood</option>
                                        <option value="urban">Urban</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_students" class="form-label">Maximum Students</label>
                                    <input type="number" class="form-control" id="max_students" name="max_students" value="20">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="instructor_name" class="form-label">Instructor Name</label>
                                    <input type="text" class="form-control" id="instructor_name" name="instructor_name">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" placeholder="e.g., img/course-name.jpg">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Course
                        </button>
                    </form>
                </div>
            </div>

            <!-- Courses Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-book"></i> Course Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Level</th>
                                    <th>Category</th>
                                    <th>Duration</th>
                                    <th>Fees</th>
                                    <th>Enrolled/Max</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td><?php echo $course['id']; ?></td>
                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($course['level']); ?></span></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($course['dance_style']); ?></span></td>
                                        <td><?php echo $course['duration_weeks']; ?> weeks</td>
                                        <td>₹<?php echo number_format($course['price']); ?></td>
                                        <td>
                                            <span class="<?php echo $course['enrolled_count'] >= $course['max_students'] ? 'text-danger' : 'text-success'; ?>">
                                                <?php echo $course['enrolled_count']; ?>/<?php echo $course['max_students']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php
                                                echo $course['is_active'] ? 'bg-success' : 'bg-danger';
                                            ?>">
                                                <?php echo $course['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editCourse(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($courses)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No courses found</h5>
                            <p class="text-muted">Add your first course using the form above</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'schedules'): ?>
            <?php
            // Handle edit mode
            $edit_schedule = null;
            if (isset($_GET['edit'])) {
                $edit_id = intval($_GET['edit']);
                foreach ($schedules as $schedule) {
                    if ($schedule['id'] == $edit_id) {
                        $edit_schedule = $schedule;
                        break;
                    }
                }
            }
            ?>

            <!-- Add/Edit Schedule Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-<?php echo $edit_schedule ? 'edit' : 'plus'; ?>"></i> <?php echo $edit_schedule ? 'Edit Schedule' : 'Add New Schedule'; ?></h5>
                    <?php if ($edit_schedule): ?>
                        <a href="?tab=schedules" class="btn btn-sm btn-secondary ms-2">Cancel Edit</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $edit_schedule ? 'update_schedule' : 'add_schedule'; ?>">
                        <?php if ($edit_schedule): ?>
                            <input type="hidden" name="schedule_id" value="<?php echo $edit_schedule['id']; ?>">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="course_name" class="form-label">Course *</label>
                                    <select class="form-control select2-course" id="course_name" name="course_name" required>
                                        <option value="">-- Select Course --</option>
                                        <?php foreach ($courses_dropdown as $course): ?>
                                            <option value="<?php echo htmlspecialchars($course['name']); ?>" <?php echo ($edit_schedule && $edit_schedule['course_name'] == $course['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($course['name'] . ' (' . $course['dance_style'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="instructor_name" class="form-label">Instructor *</label>
                                    <select class="form-control select2-instructor" id="instructor_name" name="instructor_name" required>
                                        <option value="">-- Select Instructor --</option>
                                        <?php foreach ($instructors_dropdown as $instructor): ?>
                                            <option value="<?php echo htmlspecialchars($instructor['name']); ?>" <?php echo ($edit_schedule && $edit_schedule['instructor_name'] == $instructor['name']) ? 'selected' : ''; ?>>
                                                <?php
                                                $display_text = htmlspecialchars($instructor['name']);
                                                if (!empty($instructor['specialization'])) {
                                                    $display_text .= ' (' . htmlspecialchars($instructor['specialization']) . ')';
                                                }
                                                if (!empty($instructor['experience_years'])) {
                                                    $display_text .= ' - ' . $instructor['experience_years'] . ' years exp';
                                                }
                                                if (!empty($instructor['rating'])) {
                                                    $display_text .= ' - Rating: ' . number_format($instructor['rating'], 1);
                                                }
                                                echo $display_text;
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="day_of_week" class="form-label">Day of Week *</label>
                                    <select class="form-control" id="day_of_week" name="day_of_week" required>
                                        <option value="">-- Select Day --</option>
                                        <option value="Monday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
                                        <option value="Tuesday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                                        <option value="Wednesday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                                        <option value="Thursday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                                        <option value="Friday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
                                        <option value="Saturday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
                                        <option value="Sunday" <?php echo ($edit_schedule && $edit_schedule['day_of_week'] == 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time *</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo $edit_schedule ? $edit_schedule['start_time'] : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time *</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo $edit_schedule ? $edit_schedule['end_time'] : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="max_capacity" class="form-label">Maximum Capacity</label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" value="<?php echo $edit_schedule ? $edit_schedule['max_capacity'] : '20'; ?>" min="1" max="100">
                                </div>
                                <div class="mb-3">
                                    <label for="room_number" class="form-label">Room Number</label>
                                    <input type="text" class="form-control" id="room_number" name="room_number" value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['room_number']) : ''; ?>" placeholder="e.g., Room 101">
                                </div>
                                <div class="mb-3">
                                    <label for="meeting_link" class="form-label">Online Meeting Link</label>
                                    <input type="url" class="form-control" id="meeting_link" name="meeting_link"
                                           value="<?php echo $edit_schedule ? htmlspecialchars($edit_schedule['meeting_link'] ?? '') : ''; ?>"
                                           placeholder="https://zoom.us/... or https://meet.google.com/...">
                                    <div class="form-text">Add Zoom or Google Meet link for online classes</div>
                                </div>
                                <?php if ($edit_schedule): ?>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $edit_schedule['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active Schedule
                                        </label>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-<?php echo $edit_schedule ? 'save' : 'plus'; ?>"></i> <?php echo $edit_schedule ? 'Update Schedule' : 'Add Schedule'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Schedules Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-alt"></i> Schedule Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course</th>
                                    <th>Instructor</th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Capacity</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo $schedule['id'] ?? 'N/A'; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($schedule['course_name'] ?? 'N/A'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($schedule['dance_style'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($schedule['profile_image'] ?? 'img/male-instructor.jpg'); ?>"
                                                 alt="<?php echo htmlspecialchars($schedule['instructor_name']); ?>"
                                                 class="rounded-circle me-2"
                                                 style="width: 35px; height: 35px; object-fit: cover;">
                                            <div>
                                                <strong><?php echo htmlspecialchars($schedule['instructor_name'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($schedule['specialization'] ?? '')): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($schedule['specialization'] ?? ''); ?></small>
                                                <?php endif; ?>
                                                <?php if (!empty($schedule['experience_years'] ?? 0)): ?>
                                                    <br><small class="text-muted"><?php echo $schedule['experience_years'] ?? 0; ?> years exp</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($schedule['day_of_week'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(($schedule['start_time'] ?? 'N/A') . ' - ' . ($schedule['end_time'] ?? 'N/A')); ?></td>
                                    <td><?php echo $schedule['duration_minutes'] ?? 0; ?> min</td>
                                    <td><?php echo $schedule['max_capacity'] ?? 0; ?></td>
                                    <td><?php echo htmlspecialchars($schedule['room_number'] ?: 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($schedule['is_active'] ?? 0) ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ($schedule['is_active'] ?? 0) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editSchedule(<?php echo $schedule['id'] ?? 0; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteSchedule(<?php echo $schedule['id'] ?? 0; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($schedules)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No schedules found</h5>
                            <p class="text-muted">Add your first schedule using the form above</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'enrollments'): ?>
            <!-- Enrollments Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-graduation-cap"></i> Enrollment Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Course</th>
                                    <th>Enrolled Date</th>
                                    <th>Payment Status</th>
                                    <th>Course Status</th>
                                    <th>Progress</th>
                                    <th>Certificate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                <tr>
                                    <td><?php echo $enrollment['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($enrollment['student_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                    <td>
                                        <span class="badge <?php
                                            echo $enrollment['payment_status'] === 'paid' ? 'bg-success' :
                                                ($enrollment['payment_status'] === 'failed' ? 'bg-danger' : 'bg-warning');
                                        ?>">
                                            <?php echo ucfirst($enrollment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php
                                            echo $enrollment['status'] === 'completed' ? 'bg-success' :
                                                ($enrollment['status'] === 'active' ? 'bg-info' : 'bg-primary');
                                        ?>">
                                            <?php echo ucfirst($enrollment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width: <?php echo $enrollment['progress_percentage']; ?>%"
                                                 aria-valuenow="<?php echo $enrollment['progress_percentage']; ?>"
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo number_format($enrollment['progress_percentage'], 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($enrollment['status'] === 'completed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editEnrollment(<?php echo $enrollment['id']; ?>)" data-bs-toggle="modal" data-bs-target="#editEnrollmentModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($enrollments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No enrollments found</h5>
                            <p class="text-muted">Students will appear here once they enroll in courses</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'bookings'): ?>
            <!-- Bookings Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-check"></i> Class Bookings</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Course</th>
                                    <th>Class Date & Time</th>
                                    <th>Instructor</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Booked At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['course_name'] ?? 'N/A'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['dance_style']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($booking['class_date'] && $booking['day_of_week']): ?>
                                            <strong><?php echo date('M d, Y', strtotime($booking['class_date'])); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['day_of_week']); ?> • <?php echo htmlspecialchars($booking['start_time'] . ' - ' . $booking['end_time']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['instructor_name'] ?: 'TBD'); ?></td>
                                    <td><?php echo htmlspecialchars($booking['room_number'] ?: 'TBD'); ?></td>
                                    <td>
                                        <span class="badge <?php
                                            echo $booking['status'] === 'confirmed' ? 'bg-success' :
                                                ($booking['status'] === 'cancelled' ? 'bg-danger' :
                                                ($booking['status'] === 'completed' ? 'bg-info' : 'bg-warning'));
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        // Get payment status from enrollments table
                                        $payment_status = 'N/A';
                                        if ($booking['user_id'] && $booking['course_id']) {
                                            $payment_stmt = $conn->prepare("SELECT payment_status FROM enrollments WHERE user_id = ? AND course_id = ?");
                                            $payment_stmt->bind_param("ii", $booking['user_id'], $booking['course_id']);
                                            $payment_stmt->execute();
                                            $payment_result = $payment_stmt->get_result();
                                            $enrollment = $payment_result->fetch_assoc();
                                            if ($enrollment) {
                                                $payment_status = $enrollment['payment_status'];
                                            }
                                        }
                                        ?>
                                        <span class="badge <?php
                                            echo $payment_status === 'paid' ? 'bg-success' :
                                                ($payment_status === 'pending' ? 'bg-warning' :
                                                ($payment_status === 'failed' ? 'bg-danger' : 'bg-secondary'));
                                        ?>">
                                            <?php echo ucfirst($payment_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No bookings found</h5>
                            <p class="text-muted">Class bookings will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'messages'): ?>
            <!-- Messages Table -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-envelope"></i> Message Management</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sender</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><?php echo $message['id']; ?></td>
                                    <td>
                                        <?php if ($message['sender_name']): ?>
                                            <strong><?php echo htmlspecialchars($message['sender_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['sender_email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Anonymous</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo ucfirst($message['message_type']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $message['status'] === 'read' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($message['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($message['sent_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($messages)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No messages found</h5>
                            <p class="text-muted">Messages from contact forms will appear here</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($current_tab === 'statistics'): ?>
            <!-- Statistics Dashboard -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['total_courses']; ?></h4>
                                    <p class="mb-0">Total Courses</p>
                                </div>
                                <div>
                                    <i class="fas fa-book fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['total_instructors']; ?></h4>
                                    <p class="mb-0">Instructors</p>
                                </div>
                                <div>
                                    <i class="fas fa-users-cog fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['total_enrollments']; ?></h4>
                                    <p class="mb-0">Total Enrollments</p>
                                </div>
                                <div>
                                    <i class="fas fa-graduation-cap fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['completed_enrollments']; ?></h4>
                                    <p class="mb-0">Completed</p>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['total_certificates']; ?></h4>
                                    <p class="mb-0">Certificates Issued</p>
                                </div>
                                <div>
                                    <i class="fas fa-certificate fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo count($users); ?></h4>
                                    <p class="mb-0">Total Users</p>
                                </div>
                                <div>
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['total_bookings']; ?></h4>
                                    <p class="mb-0">Total Bookings</p>
                                </div>
                                <div>
                                    <i class="fas fa-calendar-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4><?php echo $stats['upcoming_bookings']; ?></h4>
                                    <p class="mb-0">Upcoming Classes</p>
                                </div>
                                <div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="?tab=courses" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Add New Course
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?tab=instructors" class="btn btn-info w-100">
                                <i class="fas fa-user-plus"></i> Add New Instructor
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="?tab=enrollments" class="btn btn-success w-100">
                                <i class="fas fa-users"></i> Manage Enrollments
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="classes.php" class="btn btn-secondary w-100" target="_blank">
                                <i class="fas fa-eye"></i> View Public Courses
                            </a>
                        </div>
                    </div>

                    <!-- Google Meet Link Setup -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="fab fa-google me-2"></i>Google Meet Link Setup</h6>
                                <p class="mb-2">Add Google Meet link to all active class schedules for September 17-30, 2025</p>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="add_google_meet_link">
                                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Add Google Meet link to all active schedules?')">
                                        <i class="fab fa-google me-1"></i> Add Meet Link
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-1">
                                    Link: https://meet.google.com/hxb-vxox-ccd | Time Zone: Asia/Kolkata
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Enrollment Modal -->
    <div class="modal fade" id="editEnrollmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_enrollment">
                        <input type="hidden" name="enrollment_id" id="edit_enrollment_id">
                        
                        <div class="mb-3">
                            <label for="edit_payment_status" class="form-label">Payment Status</label>
                            <select class="form-control" id="edit_payment_status" name="payment_status">
                                <option value="">-- Select --</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="failed">Failed</option>
                                <option value="refunded">Refunded</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_course_status" class="form-label">Course Status</label>
                            <select class="form-control" id="edit_course_status" name="course_status">
                                <option value="">-- Select --</option>
                                <option value="enrolled">Enrolled</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="dropped">Dropped</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_progress_percentage" class="form-label">Progress Percentage</label>
                            <input type="number" class="form-control" id="edit_progress_percentage" name="progress_percentage" min="0" max="100" step="0.1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_attended_classes" class="form-label">Attended Classes</label>
                            <input type="number" class="form-control" id="edit_attended_classes" name="attended_classes" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Schedule Modal -->
    <div class="modal fade" id="editScheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_schedule">
                        <input type="hidden" name="schedule_id" id="edit_schedule_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_course_name" class="form-label">Course *</label>
                                    <select class="form-control select2-course" id="edit_course_name" name="course_name" required>
                                        <option value="">-- Select Course --</option>
                                        <?php foreach ($courses_dropdown as $course): ?>
                                            <option value="<?php echo htmlspecialchars($course['name']); ?>">
                                                <?php echo htmlspecialchars($course['name'] . ' (' . $course['dance_style'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_name" class="form-label">Instructor *</label>
                                    <select class="form-control select2-instructor" id="edit_instructor_name" name="instructor_name" required>
                                        <option value="">-- Select Instructor --</option>
                                        <?php foreach ($instructors_dropdown as $instructor): ?>
                                            <option value="<?php echo htmlspecialchars($instructor['name']); ?>">
                                                <?php
                                                $display_text = htmlspecialchars($instructor['name']);
                                                if (!empty($instructor['specialization'])) {
                                                    $display_text .= ' (' . htmlspecialchars($instructor['specialization']) . ')';
                                                }
                                                if (!empty($instructor['experience_years'])) {
                                                    $display_text .= ' - ' . $instructor['experience_years'] . ' years exp';
                                                }
                                                if (!empty($instructor['rating'])) {
                                                    $display_text .= ' - Rating: ' . number_format($instructor['rating'], 1);
                                                }
                                                echo $display_text;
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_day_of_week" class="form-label">Day of Week *</label>
                                    <select class="form-control" id="edit_day_of_week" name="day_of_week" required>
                                        <option value="">-- Select Day --</option>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_time" class="form-label">Start Time *</label>
                                    <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_end_time" class="form-label">End Time *</label>
                                    <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_max_capacity" class="form-label">Maximum Capacity</label>
                                    <input type="number" class="form-control" id="edit_max_capacity" name="max_capacity" min="1" max="100">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_room_number" class="form-label">Room Number</label>
                                    <input type="text" class="form-control" id="edit_room_number" name="room_number">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" checked>
                                        <label class="form-check-label" for="edit_is_active">
                                            Active Schedule
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_course">
                        <input type="hidden" name="course_id" id="edit_course_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_course_title" class="form-label">Course Title *</label>
                                    <input type="text" class="form-control" id="edit_course_title" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_course_description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_fees" class="form-label">Fees (₹) *</label>
                                    <input type="number" class="form-control" id="edit_course_fees" name="fees" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_level" class="form-label">Level</label>
                                    <select class="form-control" id="edit_course_level" name="level">
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                        <option value="All Levels">All Levels</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_course_duration" class="form-label">Duration (Weeks)</label>
                                    <input type="number" class="form-control" id="edit_course_duration" name="duration_weeks" value="12">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_category" class="form-label">Category</label>
                                    <select class="form-control" id="edit_course_category" name="category">
                                        <option value="classical">Classical</option>
                                        <option value="contemporary">Contemporary</option>
                                        <option value="folk">Folk</option>
                                        <option value="bollywood">Bollywood</option>
                                        <option value="urban">Urban</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_max_students" class="form-label">Maximum Students</label>
                                    <input type="number" class="form-control" id="edit_course_max_students" name="max_students" value="20">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_image_url" class="form-label">Image URL</label>
                                    <input type="text" class="form-control" id="edit_course_image_url" name="image_url" placeholder="e.g., img/course-name.jpg">
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_course_is_active" name="is_active" checked>
                                        <label class="form-check-label" for="edit_course_is_active">
                                            Active Course
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Instructor Modal -->
    <div class="modal fade" id="editInstructorModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Instructor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_instructor">
                        <input type="hidden" name="instructor_id" id="edit_instructor_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_instructor_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="edit_instructor_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="edit_instructor_email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="edit_instructor_phone" name="phone">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_specialization" class="form-label">Dance Specialization</label>
                                    <select class="form-control" id="edit_instructor_specialization" name="specialization">
                                        <option value="">-- Select Specialization --</option>
                                        <option value="Bharatanatyam">Bharatanatyam</option>
                                        <option value="Kathak">Kathak</option>
                                        <option value="Hip-Hop">Hip-Hop</option>
                                        <option value="Salsa">Salsa</option>
                                        <option value="Contemporary">Contemporary</option>
                                        <option value="Ballet">Ballet</option>
                                        <option value="Odissi">Odissi</option>
                                        <option value="Folk">Folk</option>
                                        <option value="Bollywood">Bollywood</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_profile_image" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control" id="edit_instructor_profile_image" name="profile_image" accept="image/*">
                                    <div class="form-text">Upload a new profile photo (JPG, PNG, GIF). Leave empty to keep current photo. Max file size: 5MB</div>
                                    <div id="current_profile_image" class="mt-2">
                                        <small class="text-muted">Current profile image will be displayed here</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_instructor_experience" class="form-label">Years of Experience</label>
                                    <input type="number" class="form-control" id="edit_instructor_experience" name="experience_years" min="0" max="50">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_rate" class="form-label">Hourly Rate (₹)</label>
                                    <input type="number" class="form-control" id="edit_instructor_rate" name="hourly_rate" step="0.01" min="0">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_instructor_bio" class="form-label">Bio/Description</label>
                                    <textarea class="form-control" id="edit_instructor_bio" name="bio" rows="3" placeholder="Brief description about the instructor..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2-course').select2({
                tags: true,
                placeholder: "Select or type a course name",
                allowClear: true,
                width: '100%'
            });
            $('.select2-instructor').select2({
                tags: true,
                placeholder: "Select or type an instructor name",
                allowClear: true,
                width: '100%',
                matcher: function(params, data) {
                    // Custom matcher to search within the display text
                    if ($.trim(params.term) === '') {
                        return data;
                    }
                    if (typeof data.text === 'undefined') {
                        return null;
                    }
                    // Case-insensitive search
                    if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                        return data;
                    }
                    return null;
                }
            });
        });

        function editEnrollment(enrollmentId) {
            document.getElementById('edit_enrollment_id').value = enrollmentId;
        }
        
        function editCourse(courseId) {
            // Fetch course data and populate the edit modal
            fetch('php/get_course.php?course_id=' + courseId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const course = data.course;
                        console.log('Loaded course data:', course);
                        console.log('Setting category to:', course.dance_style);

                        document.getElementById('edit_course_id').value = course.id;
                        document.getElementById('edit_course_title').value = course.name;
                        document.getElementById('edit_course_description').value = course.description || '';
                        document.getElementById('edit_course_fees').value = course.price;
                        document.getElementById('edit_course_level').value = course.level;
                        document.getElementById('edit_course_duration').value = course.duration_weeks;
                        document.getElementById('edit_course_category').value = course.dance_style;
                        document.getElementById('edit_course_max_students').value = course.max_students;
                        document.getElementById('edit_course_image_url').value = course.image_url || '';
                        document.getElementById('edit_course_is_active').checked = course.is_active == 1;

                        // Verify the category was set correctly
                        const categorySelect = document.getElementById('edit_course_category');
                        console.log('Category select value after setting:', categorySelect.value);
                        console.log('Available options:', Array.from(categorySelect.options).map(opt => opt.value));

                        // Add form submission debugging
                        const editForm = document.getElementById('editCourseModal').querySelector('form');
                        editForm.addEventListener('submit', function(e) {
                            const formData = new FormData(editForm);
                            console.log('Form submission data:');
                            for (let [key, value] of formData.entries()) {
                                console.log(key + ': ' + value);
                            }
                        });

                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('editCourseModal'));
                        modal.show();
                    } else {
                        alert('Error loading course data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading course data');
                });
        }

        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_course">
                    <input type="hidden" name="course_id" value="${courseId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function editInstructor(instructorId) {
            // Fetch instructor data and populate the edit modal
            fetch('php/get_instructor.php?instructor_id=' + instructorId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const instructor = data.instructor;
                        document.getElementById('edit_instructor_id').value = instructor.id;
                        document.getElementById('edit_instructor_name').value = instructor.name;
                        document.getElementById('edit_instructor_email').value = instructor.email;
                        document.getElementById('edit_instructor_phone').value = instructor.phone || '';
                        document.getElementById('edit_instructor_specialization').value = instructor.specialization || '';
                        document.getElementById('edit_instructor_experience').value = instructor.experience_years || 0;
                        document.getElementById('edit_instructor_rate').value = instructor.hourly_rate || 0;
                        document.getElementById('edit_instructor_bio').value = instructor.bio || '';

                        // Show current profile image
                        const currentImageDiv = document.getElementById('current_profile_image');
                        if (instructor.profile_image) {
                            currentImageDiv.innerHTML = `
                                <img src="${instructor.profile_image}" alt="Current profile" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 2px solid #667eea;">
                                <br><small class="text-muted">Current profile image</small>
                            `;
                        } else {
                            currentImageDiv.innerHTML = '<small class="text-muted">No profile image uploaded</small>';
                        }

                        // Show the modal
                        const modal = new bootstrap.Modal(document.getElementById('editInstructorModal'));
                        modal.show();
                    } else {
                        alert('Error loading instructor data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading instructor data');
                });
        }

        function deleteInstructor(instructorId) {
            if (confirm('Are you sure you want to delete this instructor? This will also remove their user account and may affect existing schedules.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_instructor">
                    <input type="hidden" name="instructor_id" value="${instructorId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function editSchedule(scheduleId) {
            // For now, redirect with schedule ID to edit
            window.location.href = '?tab=schedules&edit=' + scheduleId;
        }

        function deleteSchedule(scheduleId) {
            if (confirm('Are you sure you want to delete this schedule? This action cannot be undone and may affect existing bookings.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_schedule">
                    <input type="hidden" name="schedule_id" value="${scheduleId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

    </script>
</body>
</html>