<?php
/**
 * Contact Us Page - TaalBeats Dance Academy
 */
session_start();
// Include database connection
require_once 'php/db.php';

// Handle form submission
$message_display = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message_content = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $message_display = '<div class="alert alert-danger">All fields are required.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_display = '<div class="alert alert-danger">Please enter a valid email address.</div>';
    } else {
        // Sanitize inputs
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $message_content = htmlspecialchars($message_content, ENT_QUOTES, 'UTF-8');

        // Prepare the message content with name and email
        $full_message = "From: {$name} <{$email}>\n\n{$message_content}";

        try {
            // Insert into messages table
            // Using admin user ID (1) as both sender and receiver for contact form messages
            $sender_id = 1;
            $receiver_id = 1;
            $stmt = $conn->prepare("
                INSERT INTO messages (sender_id, receiver_id, subject, message, message_type, sent_at)
                VALUES (?, ?, ?, ?, 'inquiry', NOW())
            ");
            $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $full_message);
            $result = $stmt->execute();

            if ($result) {
                $message_display = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Message sent successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            } else {
                $message_display = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Failed to send message. Try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $message_display = '<div class="alert alert-danger">Failed to send message. Try again.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - TaalBeats Academy</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Yantramanav:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Navbar: remove underline and hover effects + dropdown sizing */
        .navbar .nav-link,
        .navbar .dropdown-item { text-decoration: none !important; }
        .navbar .nav-link:hover,
        .navbar .dropdown-item:hover { text-decoration: none !important; background: transparent !important; color: inherit !important; }
        .profile-dropdown .dropdown-menu { min-width: 250px; }
        :root {
            --main-bg: #fdf2f8;
            --accent: #e83e8c;
            --accent-dark: #d63384;
            --card-bg: #ffffff;
            --text-dark: #333;
            --text-muted: #666;
        }
        
        body {
            background: var(--main-bg);
            color: var(--text-dark);
            font-family: 'Roboto', Arial, sans-serif;
        }
        
        .contact-hero {
            background: linear-gradient(135deg, rgba(232,62,140,0.1) 0%, rgba(253,242,248,0.95) 100%), url('img/breadcrumb.jpg') center/cover no-repeat;
            padding: 120px 0 80px;
            text-align: center;
        }
        
        .contact-hero h1 {
            font-family: 'Yantramanav', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent-dark);
        }
        
        .contact-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .contact-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .static-hero {
            position: relative;
        }

        .static-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .static-hero .carousel-caption {
            z-index: 2;
        }
    </style>
</head>
<body>
    <!-- Navbar Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
            <a href="index.php" class="navbar-brand p-0">
                <h1 class="site-title text-primary m-0"><img src="img/logo.png" alt="Logo" height="150" width="70">taalbeats</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Home</a>
                    <a href="about.php" class="nav-item nav-link">About</a>
                    <a href="dance-style.php" class="nav-item nav-link">Dance Style</a>
                    <a href="contact.php" class="nav-item nav-link active">Contact Us</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="pagesDropdown" onclick="togglePagesDropdown(event)" onmouseenter="openPagesOnHover()" onmouseleave="noop()">Pages</a>
                        <div class="dropdown-menu m-0" id="pagesDropdownMenu" style="display: none;" onmouseenter="keepPagesOpen()" onmouseleave="closePagesOnHover()">
                            <a href="instructors.php" class="dropdown-item">Instructors</a>
                            <a href="schedule.php" class="dropdown-item">Schedule</a>
                            <a href="classes.php" class="dropdown-item">Classes</a>
                            <a href="gallery.php" class="dropdown-item">Gallery</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile Menu (right side) -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="nav-item dropdown profile-dropdown ms-3">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" onclick="toggleProfileDropdown(event)" onmouseenter="openProfileOnHover()" onmouseleave="noop()">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="profileDropdownMenu" style="display: none;" onmouseenter="keepProfileOpen()" onmouseleave="closeProfileOnHover()">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="ms-3">
                        <a href="login.html" class="btn btn-outline-primary rounded-pill py-2 px-4 me-2">Login</a>
                        <a href="register.html" class="btn btn-primary rounded-pill py-2 px-4">Register Now</a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

    <!-- Hero Section -->
    <div class="static-hero position-relative">
        <img src="img/hero.jpg" class="img-fluid w-100" alt="Contact TaalBeats Hero Image" style="height: 60vh; object-fit: cover;">

        <div class="carousel-caption position-absolute bottom-0 start-0 w-100 text-center" style="padding: 60px 20px 40px 20px;">
            <div class="carousel-caption-content p-3" style="max-width: 900px; margin: 0 auto;">
                <h1 class="display-1 site-title text-capitalize text-white mb-4 fadeInUp" style="font-size: 3.5rem;">Contact Us</h1>
                <p class="fs-5 text-white fadeInUp mb-4" style="font-size: 1.2rem;">Get in touch with our team - we'd love to hear from you!</p>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="contact-card">
                        <h3 class="mb-4" style="color: var(--accent-dark);">Send us a Message</h3>
                        <form method="post">
                            <?php echo $message_display; ?>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <select class="form-control" id="subject" name="subject">
                                        <option>General Inquiry</option>
                                        <option>Course Information</option>
                                        <option>Enrollment</option>
                                        <option>Schedule</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">Send Message</button>
                        </form>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <div class="contact-card">
                        <h3 class="mb-4" style="color: var(--accent-dark);">Get in Touch</h3>
                        
                        <div class="contact-info-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h6>Address</h6>
                                <p class="mb-0 text-muted">123 Dance Street, Art District, Mumbai, Maharashtra 400001</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h6>Phone</h6>
                                <p class="mb-0 text-muted">+91 98765 43210</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h6>Email</h6>
                                <p class="mb-0 text-muted">info@taalbeats.com</p>
                            </div>
                        </div>

                        <div class="contact-info-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h6>Hours</h6>
                                <p class="mb-0 text-muted">Mon-Sat: 9AM-8PM<br>Sunday: 10AM-6PM</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="contact-card">
                        <h5 class="mb-3" style="color: var(--accent-dark);">Follow Us</h5>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-decoration-none">
                                <div class="contact-icon">
                                    <i class="fab fa-facebook-f"></i>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="contact-icon">
                                    <i class="fab fa-instagram"></i>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="contact-icon">
                                    <i class="fab fa-youtube"></i>
                                </div>
                            </a>
                            <a href="#" class="text-decoration-none">
                                <div class="contact-icon">
                                    <i class="fab fa-twitter"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5" style="background: white;">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="contact-card">
                        <h3 class="mb-4 text-center" style="color: var(--accent-dark);">Find Us</h3>
                        <div class="ratio ratio-21x9">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3771.9999999999995!2d72.8777!3d19.0760!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTnCsDA0JzMzLjYiTiA3MsKwNTInMzkuNyJF!5e0!3m2!1sen!2sin!4v1234567890" 
                                    style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <!-- Follow Us Section -->
        <div class="container py-2">
            <div class="row">
                <div class="col-12 text-center">
                    <h6 class="text-white mb-2">Follow Us</h6>
                    <div class="d-flex justify-content-center gap-2">
                        <a class="btn btn-square btn-primary rounded-circle btn-sm" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle btn-sm" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle btn-sm" href=""><i class="fab fa-instagram"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle btn-sm" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle btn-sm" href=""><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div class="container py-2">
            <div class="row g-2">
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white mb-1">Get In Touch</h6>
                    <p class="mb-1 small"><i class="fa fa-map-marker-alt me-2"></i>123 Dance Street, Art District, Mumbai</p>
                    <p class="mb-1 small"><i class="fa fa-phone me-2"></i>+91 98765 43210</p>
                    <p class="mb-1 small"><i class="fa fa-envelope me-2"></i>info@taalbeats.com</p>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white mb-1">Quick Links</h6>
                    <div class="d-flex flex-column">
                        <a class="btn btn-link btn-sm p-0 mb-1" href="about.php">About Us</a>
                        <a class="btn btn-link btn-sm p-0 mb-1" href="contact.php">Contact Us</a>
                        <a class="btn btn-link btn-sm p-0 mb-1" href="dance-style.php">Dance style</a>
                        <a class="btn btn-link btn-sm p-0 mb-1" href="schedule.php">Schedule</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white mb-1">Newsletter</h6>
                    <p class="mb-1 small">Subscribe to our newsletter for updates.</p>
                    <div class="position-relative" style="max-width: 300px;">
                        <input class="form-control form-control-sm border-0 rounded-pill w-100 ps-3 pe-4 py-1" type="text" placeholder="Your Email" style="height: 32px;">
                        <button type="button" class="btn btn-sm shadow-none position-absolute top-0 end-0 mt-0 me-1"><i class="fa fa-paper-plane text-primary"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container py-1">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-1 mb-md-0">
                        <small>&copy; <a class="border-bottom" href="#">TaalBeats</a>, All Right Reserved.</small>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="footer-menu">
                            <a href="" class="small">Home</a>
                            <a href="" class="small">Help</a>
                            <a href="" class="small">FQAs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Pages/Profile Dropdown JavaScript -->
    <script>
    // Pages Dropdown Functionality (click + hover)
    function togglePagesDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        const dropdownMenu = document.getElementById('pagesDropdownMenu');
        const isVisible = dropdownMenu && dropdownMenu.style.display === 'block';
        closeAllDropdowns();
        if (dropdownMenu) dropdownMenu.style.display = isVisible ? 'none' : 'block';
    }
    let pagesHoverTimeout = null;
    function openPagesOnHover() { clearTimeout(pagesHoverTimeout); const m = document.getElementById('pagesDropdownMenu'); if (m) m.style.display = 'block'; }
    function keepPagesOpen() { clearTimeout(pagesHoverTimeout); }
    function closePagesOnHover() { pagesHoverTimeout = setTimeout(function(){ const m = document.getElementById('pagesDropdownMenu'); if (m) m.style.display = 'none'; }, 120); }
    function closeAllDropdowns() {
        const pages = document.getElementById('pagesDropdownMenu'); if (pages) pages.style.display = 'none';
        const profile = document.getElementById('profileDropdownMenu'); if (profile) profile.style.display = 'none';
        document.querySelectorAll('.dropdown-menu').forEach(d => { d.classList.remove('show'); d.style.display = 'none'; });
    }
    document.addEventListener('click', function(e){
        const pMenu = document.getElementById('profileDropdownMenu');
        const pTog = document.getElementById('profileDropdown');
        const pgMenu = document.getElementById('pagesDropdownMenu');
        const pgTog = document.getElementById('pagesDropdown');
        if (pgMenu && pgTog && !pgMenu.contains(e.target) && !pgTog.contains(e.target)) pgMenu.style.display = 'none';
        if (pMenu && pTog && !pMenu.contains(e.target) && !pTog.contains(e.target)) pMenu.style.display = 'none';
    });
    // Profile Dropdown
    function toggleProfileDropdown(event) { event.preventDefault(); event.stopPropagation(); const m = document.getElementById('profileDropdownMenu'); const v = m && m.style.display === 'block'; closeAllDropdowns(); if (m) m.style.display = v ? 'none' : 'block'; }
    let profileHoverTimeout = null;
    function openProfileOnHover() { clearTimeout(profileHoverTimeout); const m = document.getElementById('profileDropdownMenu'); if (m) m.style.display = 'block'; }
    function keepProfileOpen() { clearTimeout(profileHoverTimeout); }
    function closeProfileOnHover() { profileHoverTimeout = setTimeout(function(){ const m = document.getElementById('profileDropdownMenu'); if (m) m.style.display = 'none'; }, 120); }
    function noop(){}
    </script>
</body>
</html>
