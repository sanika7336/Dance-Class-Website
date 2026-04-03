<?php
/**
 * Database Setup Script
 * TaalBeats Dance Academy
 * Creates all necessary database tables
 */

// Include database connection
require_once 'db.php';

try {
    // Create users table
    $conn->query("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(15) NOT NULL,
            password VARCHAR(255) NOT NULL,
            dance_style VARCHAR(50),
            is_admin BOOLEAN DEFAULT FALSE,
            registered_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_dance_style (dance_style)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Users table created successfully<br>";
    
    // Create instructors table
    $conn->query("
        CREATE TABLE IF NOT EXISTS instructors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            phone VARCHAR(15),
            specialization VARCHAR(100),
            experience_years INT,
            bio TEXT,
            image_url VARCHAR(255),
            created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_specialization (specialization)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Instructors table created successfully<br>";
    
    // Create enrollments table
    $conn->query("
        CREATE TABLE IF NOT EXISTS enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            dance_style VARCHAR(50) NOT NULL,
            preferred_batch VARCHAR(20) NOT NULL,
            dance_experience VARCHAR(20) NOT NULL,
            payment_method VARCHAR(20) NOT NULL,
            payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
            payment_date TIMESTAMP NULL,
            enrolled_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_dance_style (dance_style),
            INDEX idx_payment_status (payment_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Enrollments table created successfully<br>";
    
    // Create schedule table
    $conn->query("
        CREATE TABLE IF NOT EXISTS schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            day VARCHAR(20) NOT NULL,
            time VARCHAR(20) NOT NULL,
            dance_style VARCHAR(50) NOT NULL,
            instructor_id INT,
            level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
            max_students INT DEFAULT 20,
            current_students INT DEFAULT 0,
            zoom_link VARCHAR(255),
            google_meet_link VARCHAR(255),
            created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (instructor_id) REFERENCES instructors(id) ON DELETE SET NULL,
            INDEX idx_day_time (day, time),
            INDEX idx_dance_style (dance_style),
            INDEX idx_level (level)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Schedule table created successfully<br>";
    
    // Create payments table
    $conn->query("
        CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            enrollment_id INT,
            amount DECIMAL(10,2) NOT NULL,
            transaction_id VARCHAR(100) UNIQUE NOT NULL,
            payment_method VARCHAR(20) NOT NULL,
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_transaction_id (transaction_id),
            INDEX idx_status (status),
            INDEX idx_date (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Payments table created successfully<br>";
    
    // Create certificates table
    $conn->query("
        CREATE TABLE IF NOT EXISTS certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            course_name VARCHAR(100) NOT NULL,
            certificate_id VARCHAR(50) UNIQUE NOT NULL,
            issued_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_certificate_id (certificate_id),
            INDEX idx_course_name (course_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Certificates table created successfully<br>";
    
    // Create messages table
    $conn->query("
        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            sent_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_is_read (is_read),
            INDEX idx_sent_on (sent_on)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Messages table created successfully<br>";
    
    // Create testimonials table
    $conn->query("
        CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT NOT NULL,
            is_approved BOOLEAN DEFAULT FALSE,
            submitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_rating (rating),
            INDEX idx_is_approved (is_approved)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Testimonials table created successfully<br>";
    
    // Create admins table
    $conn->query("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Admins table created successfully<br>";
    
    // Create notifications table
    $conn->query("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            data JSON NULL,
            is_read BOOLEAN DEFAULT FALSE,
            read_on TIMESTAMP NULL,
            created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_type (type),
            INDEX idx_is_read (is_read),
            INDEX idx_created_on (created_on)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Notifications table created successfully<br>";
    
    // Insert default admin user
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
        INSERT IGNORE INTO admins (username, password, email, full_name, role)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssss", $username, $password, $email, $full_name, $role);
    $username = 'admin';
    $password = $admin_password;
    $email = 'admin@gmail.com';
    $full_name = 'System Administrator';
    $role = 'super_admin';
    $stmt->execute();
    echo "✓ Default admin user created (email: admin@gmail.com, password: admin123)<br>";
    
    // Insert sample instructors
    $instructors = [
        ['Dr. Neena Prasad', 'neena@taalbeats.com', '9876543210', 'Mohiniyattam', 15, 'Renowned Mohiniyattam exponent with 15+ years of experience'],
        ['Margi Vijayakumar', 'vijayakumar@taalbeats.com', '9876543211', 'Kathakali', 12, 'Expert Kathakali performer and teacher'],
        ['Geeta Chandran', 'geeta@taalbeats.com', '9876543212', 'Bharatanatyam', 20, 'Celebrated Bharatanatyam dancer and choreographer'],
        ['Dr. Rajashree Warrier', 'rajashree@taalbeats.com', '9876543213', 'Bharatanatyam', 18, 'Distinguished Bharatanatyam artist and scholar']
    ];
    
    $stmt = $conn->prepare("
        INSERT IGNORE INTO instructors (name, email, phone, specialization, experience_years, bio)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssis", $name, $email, $phone, $specialization, $experience_years, $bio);

    foreach ($instructors as $instructor) {
        $name = $instructor[0];
        $email = $instructor[1];
        $phone = $instructor[2];
        $specialization = $instructor[3];
        $experience_years = $instructor[4];
        $bio = $instructor[5];
        $stmt->execute();
    }
    echo "✓ Sample instructors added successfully<br>";
    
    // Insert sample schedule
    $schedule_data = [
        ['Monday', '09:00 AM', 'Bharatanatyam', 3, 'Beginner'],
        ['Monday', '06:00 PM', 'Kathak', 4, 'Intermediate'],
        ['Tuesday', '09:00 AM', 'Hip-Hop', 1, 'Beginner'],
        ['Tuesday', '06:00 PM', 'Contemporary', 2, 'Advanced'],
        ['Wednesday', '09:00 AM', 'Bollywood', 1, 'Beginner'],
        ['Wednesday', '06:00 PM', 'Salsa', 2, 'Intermediate'],
        ['Thursday', '09:00 AM', 'Bharatanatyam', 3, 'Intermediate'],
        ['Thursday', '06:00 PM', 'Kathak', 4, 'Advanced'],
        ['Friday', '09:00 AM', 'Hip-Hop', 1, 'Intermediate'],
        ['Friday', '06:00 PM', 'Contemporary', 2, 'Beginner'],
        ['Saturday', '10:00 AM', 'Bollywood', 1, 'Advanced'],
        ['Saturday', '04:00 PM', 'Salsa', 2, 'Beginner'],
        ['Sunday', '10:00 AM', 'Bharatanatyam', 3, 'Advanced'],
        ['Sunday', '04:00 PM', 'Kathak', 4, 'Beginner']
    ];
    
    $stmt = $conn->prepare("
        INSERT IGNORE INTO schedule (day, time, dance_style, instructor_id, level)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssii", $day, $time, $dance_style, $instructor_id, $level);

    foreach ($schedule_data as $class) {
        $day = $class[0];
        $time = $class[1];
        $dance_style = $class[2];
        $instructor_id = $class[3];
        $level = $class[4];
        $stmt->execute();
    }
    echo "✓ Sample schedule added successfully<br>";
    
    echo "<br><strong>Database setup completed successfully!</strong><br>";
    echo "You can now use the admin panel with:<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<br><a href='admin_panel.php'>Go to Admin Panel</a>";
    
} catch (PDOException $e) {
    echo "Error setting up database: " . $e->getMessage();
}
?> 