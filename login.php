<?php
/**
 * User Login Handler
 * TaalBeats Dance Academy
 * Handles user login from login.html
 */

// Start session
session_start();

// Include database connection
require_once 'db.php';

// Check if this is a password reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    // Handle password reset
    $email = sanitize_input($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    // Validate inputs
    if (empty($email) || !validate_email($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit();
    }

    if (empty($new_password) || strlen($new_password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
        exit();
    }

    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            $update_stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Password reset successful!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found with this email address.']);
        }
    } catch (Exception $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Password reset failed. Please try again.']);
    }
    exit();
}

// Handle test account creation (temporary for debugging)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_test_account'])) {
    try {
        // Check if test account already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $test_email);
        $test_email = 'test@example.com';
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();

        if (!$existing) {
            // Create test account
            $hashed_password = password_hash('test123', PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, phone, password, dance_style, registered)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $dance_style);
            $name = 'Test User';
            $email = 'test@example.com';
            $phone = '9876543210';
            $dance_style = 'Bharatanatyam';
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Test account created! Email: test@example.com, Password: test123']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Test account already exists.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to create test account: ' . $e->getMessage()]);
    }
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../login.html', 'Invalid request method.', 'error');
}

// Get and sanitize form data
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
$errors = [];

// Email validation
if (empty($email) || !validate_email($email)) {
    $errors[] = "Please enter a valid email address.";
}

// Password validation
if (empty($password)) {
    $errors[] = "Please enter your password.";
}

// If there are validation errors, redirect back
if (!empty($errors)) {
    $message = implode(' ', $errors);
    header("Location: ../login.html?status=error&message=" . urlencode($message));
    exit();
}

// Check user credentials
try {
    $stmt = $conn->prepare("
        SELECT id, name, email, password, is_admin
        FROM users
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'] ?? false;
        $_SESSION['login_time'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Clear any previous errors
        unset($_SESSION['login_errors']);
        unset($_SESSION['login_email']);

        // Redirect based on user type
        if ($user['is_admin']) {
            redirect_with_message('../admin_dashboard.php', 'Welcome back, Admin!', 'success');
        } else {
            redirect_with_message('../profile.php', 'Welcome back, ' . $user['name'] . '!', 'success');
        }

    } else {
        // Invalid credentials
        header("Location: ../login.html?status=error&message=" . urlencode('Invalid email or password.'));
        exit();
    }

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    header("Location: ../login.html?status=error&message=" . urlencode('Login failed. Please try again.'));
    exit();
}
?> 