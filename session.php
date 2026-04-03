<?php
/**
 * Session Management File
 * TaalBeats Dance Academy
 * Handles user authentication and session security
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/db.php';

/**
 * Check if user is logged in
 * Redirect to login page if not authenticated
 */
function require_login() {
    if (!is_logged_in()) {
        redirect_with_message('../login.html', 'Please login to access this page.', 'error');
    }
}

/**
 * Check if user is admin
 * Redirect to login page if not admin
 */
function require_admin() {
    if (!is_logged_in() || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
        redirect_with_message('../login.html', 'Access denied. Admin privileges required.', 'error');
    }
}

/**
 * Get current user data
 */
function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, name, email, phone, dance_style, registered_on FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        return null;
    }
}

/**
 * Update user session data
 */
function update_session_data($user_id) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("SELECT id, name, email, is_admin FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'] ?? false;
            $_SESSION['login_time'] = time();
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error updating session: " . $e->getMessage());
        return false;
    }
}

/**
 * Check session timeout (30 minutes)
 */
function check_session_timeout() {
    $timeout = 30 * 60; // 30 minutes
    
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $timeout) {
        session_destroy();
        redirect_with_message('../login.html', 'Session expired. Please login again.', 'error');
    }
    
    // Update login time on each request
    $_SESSION['login_time'] = time();
}

/**
 * Regenerate session ID for security
 */
function regenerate_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// Check session timeout on each request
check_session_timeout();
?> 