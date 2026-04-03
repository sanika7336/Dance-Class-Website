<?php
/**
 * Admin Login Handler
 * Processes admin login form submission and validates admin credentials
 */

// Start session to store user data
session_start();

// Include database connection
require_once 'php/db.php';

// Check if form is submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data and sanitize inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation - check if fields are not empty
    $errors = [];

    if (empty($email)) {
        $errors[] = "Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // If validation errors exist, redirect back with error message
    if (!empty($errors)) {
        $error_message = implode(", ", $errors);
        header("Location: admin_login.html?status=error&message=" . urlencode("❌ " . $error_message));
        exit();
    }

    try {
        // Prepare SQL query to check admin credentials from admins table
        // Using prepared statements to prevent SQL injection
        $sql = "SELECT id, email, password, full_name FROM admins WHERE email = ? AND is_active = 1";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            // Prepare failed, show error
            error_log("SQL Prepare Error in admin_login.php: " . $conn->error);
            header("Location: admin_login.html?status=error&message=" . urlencode("❌ Database error. Please try again."));
            exit();
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        // Check if admin exists and verify password
        if ($admin) {
            $stored_password = $admin['password'];
            // Check if password is hashed (starts with $2y$ for bcrypt)
            $is_hashed = strpos($stored_password, '$2y$') === 0 || strpos($stored_password, '$2a$') === 0 || strpos($stored_password, '$2b$') === 0;

            if ($is_hashed) {
                $password_valid = password_verify($password, $stored_password);
            } else {
                // Plain text comparison
                $password_valid = $password === $stored_password;
            }

            if ($password_valid) {
                // Password is correct - admin login successful

                // Start session and store admin data
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['login_time'] = time();

                // Redirect to admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Password is incorrect
                header("Location: admin_login.html?status=error&message=" . urlencode("❌ Invalid email or password"));
                exit();
            }
        } else {
            // Admin not found
            header("Location: admin_login.html?status=error&message=" . urlencode("❌ Invalid email or password"));
            exit();
        }
    } catch (Exception $e) {
        // Database error
        error_log("SQL Error in admin_login.php: " . $e->getMessage());
        header("Location: admin_login.html?status=error&message=" . urlencode("❌ Database error. Please try again."));
        exit();
    }

} else {
    // If not POST request, redirect to admin login form
    header("Location: admin_login.html");
    exit();
}
?>