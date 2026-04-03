<?php
/**
 * Enhanced User Registration Handler
 * TaalBeats Dance Academy
 * Handles user registration with comprehensive database integration
 */

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../register.html', 'Invalid request method.', 'error');
}

// Get and sanitize form data
$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$dance_style = sanitize_input($_POST['dance_style'] ?? '');
$date_of_birth = $_POST['date_of_birth'] ?? null;
$address = sanitize_input($_POST['address'] ?? '');
$emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');

// Validation
$errors = [];

// Name validation
if (empty($name) || strlen($name) < 2) {
    $errors[] = "Name must be at least 2 characters long.";
}

// Email validation
if (empty($email) || !validate_email($email)) {
    $errors[] = "Please enter a valid email address.";
}

// Phone validation
if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Please enter a valid 10-digit phone number.";
}

// Password validation
if (empty($password) || strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Dance style validation
$valid_styles = ['Bharatanatyam', 'Kathak', 'Hip-Hop', 'Contemporary', 'Bollywood', 'Salsa'];
if (empty($dance_style) || !in_array($dance_style, $valid_styles)) {
    $errors[] = "Please select a valid dance style.";
}

// Date of birth validation (optional but if provided, must be valid)
if (!empty($date_of_birth)) {
    $dob = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    if (!$dob || $dob->format('Y-m-d') !== $date_of_birth) {
        $errors[] = "Please enter a valid date of birth.";
    } else {
        // Check if user is at least 5 years old
        $today = new DateTime();
        $age = $today->diff($dob)->y;
        if ($age < 5) {
            $errors[] = "Minimum age requirement is 5 years.";
        }
    }
}

// Check if email already exists
$existing_user = get_user_by_email($email);
if ($existing_user) {
    $errors[] = "Email address is already registered.";
}

// If there are errors, redirect to thank you page with errors
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'dance_style' => $dance_style,
        'date_of_birth' => $date_of_birth,
        'address' => $address,
        'emergency_contact' => $emergency_contact
    ];
    header("Location: ../thank-you.php?status=error");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare user data
$user_data = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'password' => $hashed_password,
    'role' => 'student',
    'dance_style' => $dance_style,
    'date_of_birth' => !empty($date_of_birth) ? $date_of_birth : null,
    'address' => !empty($address) ? $address : null,
    'emergency_contact' => !empty($emergency_contact) ? $emergency_contact : null
];

// Create user using enhanced function
$user_id = create_user($user_data);

if ($user_id) {
    // Get the registered timestamp for the new user
    $user = get_user_by_id($user_id);
    
    // Set session data for immediate login
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_registered'] = $user['registered'];
    $_SESSION['role'] = 'student';
    $_SESSION['is_admin'] = 0;
    $_SESSION['login_time'] = time();
    
    // Send welcome notification
    create_notification(
        $user_id, 
        'welcome', 
        'Welcome to TaalBeats Dance Academy!', 
        "Dear {$name}, welcome to TaalBeats Dance Academy! We're excited to have you join our dance community. Start exploring our courses and enroll in your favorite dance style.",
        ['dance_style' => $dance_style],
        'courses.php'
    );
    
    // Send enrollment suggestion notification based on preferred dance style
    $courses = get_all_courses(true);
    $suggested_courses = array_filter($courses, function($course) use ($dance_style) {
        return $course['dance_style'] === $dance_style;
    });
    
    if (!empty($suggested_courses)) {
        $course_names = array_column($suggested_courses, 'name');
        $course_list = implode(', ', array_slice($course_names, 0, 3));
        
        create_notification(
            $user_id,
            'enrollment',
            'Recommended Courses for You',
            "Based on your interest in {$dance_style}, we recommend these courses: {$course_list}. Click to explore and enroll!",
            ['dance_style' => $dance_style, 'suggested_courses' => $suggested_courses],
            'courses.php'
        );
    }
    
    // Clear any previous errors
    unset($_SESSION['register_errors']);
    unset($_SESSION['register_data']);
    
    // Redirect to thank you page with success message
    redirect_with_message('../thank-you.php', 'Registration successful! Welcome to TaalBeats Dance Academy. You are now logged in.', 'success');
    
} else {
    // Registration failed
    $_SESSION['register_errors'] = ['Registration failed due to a database error. Please try again.'];
    $_SESSION['register_data'] = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'dance_style' => $dance_style,
        'date_of_birth' => $date_of_birth,
        'address' => $address,
        'emergency_contact' => $emergency_contact
    ];
    header("Location: ../thank-you.php?status=error");
    exit();
}
?>