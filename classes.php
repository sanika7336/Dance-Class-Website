<?php
/**
 * Classes Page - TaalBeats Dance Academy
 */
session_start();

// Include database connection
require_once 'php/db.php';

// Default classes data
$default_classes = [
    [
        'title' => 'Classical Bharatanatyam',
        'description' => 'Learn the ancient art of Bharatanatyam, one of India\'s most revered classical dance forms. Master intricate hand gestures, facial expressions, and graceful movements.',
        'category' => 'classical',
        'level' => 'Beginner',
        'duration_months' => 6,
        'duration_weeks' => 24,
        'fees' => 8000,
        'max_students' => 15,
        'schedule' => 'Mon, Wed, Fri - 6:00 PM',
        'instructor_name' => 'Priya Sharma',
        'image_url' => 'img/bharatanatyam-class.jpg',
        'status' => 'active'
    ],
    [
        'title' => 'Bollywood Dance Fusion',
        'description' => 'Experience the vibrant world of Bollywood dance! Learn energetic choreography combining traditional Indian moves with modern dance styles.',
        'category' => 'bollywood',
        'level' => 'Intermediate',
        'duration_months' => 4,
        'duration_weeks' => 16,
        'fees' => 6000,
        'max_students' => 20,
        'schedule' => 'Tue, Thu - 7:00 PM',
        'instructor_name' => 'Rahul Kapoor',
        'image_url' => 'img/bollywood-class.jpg',
        'status' => 'active'
    ],
    [
        'title' => 'Contemporary Dance',
        'description' => 'Express your emotions through fluid movements and creative choreography. Perfect for those seeking artistic freedom and self-expression.',
        'category' => 'contemporary',
        'level' => 'Intermediate',
        'duration_months' => 5,
        'duration_weeks' => 20,
        'fees' => 7500,
        'max_students' => 12,
        'schedule' => 'Mon, Wed - 8:00 PM',
        'instructor_name' => 'Sarah Williams',
        'image_url' => 'img/contemporary-class.jpg',
        'status' => 'active'
    ],
    [
        'title' => 'Hip-Hop & Street Dance',
        'description' => 'Get your groove on with high-energy hip-hop moves! Learn breaking, popping, locking, and the latest street dance styles.',
        'category' => 'urban',
        'level' => 'Beginner',
        'duration_months' => 3,
        'duration_weeks' => 12,
        'fees' => 5000,
        'max_students' => 18,
        'schedule' => 'Sat, Sun - 4:00 PM',
        'instructor_name' => 'DJ Mike',
        'image_url' => 'img/hiphop-class.jpg',
        'status' => 'active'
    ],
    [
        'title' => 'Kathak Classical',
        'description' => 'Discover the storytelling tradition of Kathak with its intricate spins, graceful movements, and expressive gestures rooted in North Indian culture.',
        'category' => 'classical',
        'level' => 'Advanced',
        'duration_months' => 8,
        'duration_weeks' => 32,
        'fees' => 10000,
        'max_students' => 10,
        'schedule' => 'Tue, Thu, Sat - 5:00 PM',
        'instructor_name' => 'Guru Rajesh Kumar',
        'image_url' => 'img/kathak-class.jpg',
        'status' => 'active'
    ],
    [
        'title' => 'Punjabi Folk Dance',
        'description' => 'Celebrate the joy of Punjab with energetic Bhangra and graceful Giddha. Learn traditional folk moves that will make you the life of any party!',
        'category' => 'folk',
        'level' => 'Beginner',
        'duration_months' => 3,
        'duration_weeks' => 12,
        'fees' => 4500,
        'max_students' => 25,
        'schedule' => 'Fri, Sat - 6:30 PM',
        'instructor_name' => 'Simran Kaur',
        'image_url' => 'img/punjabi-folk-class.jpg',
        'status' => 'active'
    ]
];

// Function to insert default classes if they don't exist
function ensure_default_classes($conn, $default_classes) {
    try {
        // Check if courses table has any data
        $count_stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
        $count_result = $count_stmt->fetch_assoc();
        $count = $count_result['count'];

        if ($count == 0) {
            // Insert default classes
            $insert_stmt = $conn->prepare("
                INSERT INTO courses (name, description, dance_style, level, duration_weeks, price, max_students, instructor_id, image_url, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, 1, NOW())
            ");

            foreach ($default_classes as $class) {
                $insert_stmt->bind_param("sssssiiss",
                    $class['title'],
                    $class['description'],
                    $class['category'],
                    $class['level'],
                    $class['duration_weeks'],
                    $class['fees'],
                    $class['max_students'],
                    $class['image_url']
                );
                $insert_stmt->execute();
            }
        }
    } catch (Exception $e) {
        error_log("Error inserting default classes: " . $e->getMessage());
    }
}

// Ensure default classes exist
if (isset($conn)) {
    ensure_default_classes($conn, $default_classes);
}

// Get courses from database
try {
    $courses_stmt = $conn->prepare("
        SELECT c.*,
               COUNT(e.id) as enrolled_count
        FROM courses c
        LEFT JOIN enrollments e ON c.id = e.course_id AND e.status != 'dropped'
        WHERE c.is_active = 1
        GROUP BY c.id
        ORDER BY c.dance_style, c.name
    ");
    $courses_stmt->execute();
    $result = $courses_stmt->get_result();
    $courses = $result->fetch_all(MYSQLI_ASSOC);

    // If still no courses from database, use default classes for display
    if (empty($courses)) {
        $courses = array_map(function($class) {
            $class['id'] = 0; // Temporary ID for display
            $class['enrolled_count'] = rand(3, 8); // Random enrollment for demo
            return $class;
        }, $default_classes);
    }
} catch (Exception $e) {
    // If database error, use default classes
    $courses = array_map(function($class) {
        $class['id'] = 0;
        $class['enrolled_count'] = rand(3, 8);
        return $class;
    }, $default_classes);
    error_log("Error fetching courses: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dance Classes - TaalBeats Academy</title>
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
        .navbar .nav-link:hover,
        .navbar .dropdown-item:hover { text-decoration: none !important; background: transparent !important; color: inherit !important; }
        .navbar .nav-link:hover,
        .navbar .dropdown-item:hover { text-decoration: none !important; background: transparent !important; color: inherit !important; }
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
            overflow-x: hidden;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .classes-hero {
            background: linear-gradient(135deg, rgba(232,62,140,0.1) 0%, rgba(253,242,248,0.95) 100%), url('img/breadcrumb.jpg') center/cover no-repeat;
            padding: 120px 0 80px;
            text-align: center;
        }
        
        .classes-hero h1 {
            font-family: 'Yantramanav', serif;
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent-dark);
        }
        
        .class-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            height: 100%;
        }
        
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(232,62,140,0.15);
        }
        
        .class-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .class-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 250px);
        }
        
        .class-level {
            background: var(--accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            width: fit-content;
        }
        
        .class-level.beginner { background: #28a745; }
        .class-level.intermediate { background: #ffc107; color: #333; }
        .class-level.advanced { background: #dc3545; }
        
        .btn-enroll {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.3s ease;
            margin-top: auto;
            text-align: center;
            display: block;
        }
        
        .btn-enroll:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        
        .btn-enroll:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .course-info {
            flex-grow: 1;
        }
        
        .course-info h4 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }
        
        .course-price {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .filter-tabs {
            margin-bottom: 3rem;
        }
        
        .filter-btn {
            background: transparent;
            border: 2px solid var(--accent);
            color: var(--accent);
            padding: 10px 25px;
            border-radius: 25px;
            margin: 0 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active,
        .filter-btn:hover {
            background: var(--accent);
            color: white;
        }
        
        .demo-notice {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
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
                <h1 class="site-title text-primary m-0"><img src="img/logo.png" alt="Logo" height="60" width="40" style="max-width: 100%; height: auto;">taalbeats</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Home</a>
                    <a href="about.php" class="nav-item nav-link">About</a>
                    <a href="dance-style.php" class="nav-item nav-link">Dance Style</a>
                    <a href="contact.php" class="nav-item nav-link">Contact Us</a>
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
        <img src="img/hero.jpg" class="img-fluid w-100" alt="Classes TaalBeats Hero Image" style="height: 60vh; object-fit: cover;">

        <div class="carousel-caption position-absolute bottom-0 start-0 w-100 text-center" style="padding: 60px 20px 40px 20px;">
            <div class="carousel-caption-content p-3" style="max-width: 900px; margin: 0 auto;">
                <h1 class="display-1 site-title text-capitalize text-white mb-4 fadeInUp" style="font-size: 3.5rem;">Our Dance Classes</h1>
                <p class="fs-5 text-white fadeInUp mb-4" style="font-size: 1.2rem;">Discover your passion through our diverse range of dance classes</p>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Classes Section -->
    <div class="container-fluid py-5">
        <div class="container">

            <!-- Filter Tabs -->
            <div class="filter-tabs text-center">
                <button class="filter-btn active" data-filter="all">All Classes</button>
                <button class="filter-btn" data-filter="classical">Classical</button>
                <button class="filter-btn" data-filter="contemporary">Contemporary</button>
                <button class="filter-btn" data-filter="folk">Folk</button>
                <button class="filter-btn" data-filter="bollywood">Bollywood</button>
                <button class="filter-btn" data-filter="urban">Urban</button>
            </div>

            <!-- Classes Grid -->
            <div class="row" id="classesGrid">
                <?php foreach ($courses as $course): ?>
                    <div class="col-lg-4 col-md-6 class-item" data-category="<?php echo $course['dance_style']; ?>">
                        <div class="class-card">
                            <?php if (!empty($course['image_url']) && file_exists($course['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($course['image_url']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>" class="class-image">
                            <?php else: ?>
                                <img src="img/c1.jpg" alt="<?php echo htmlspecialchars($course['name']); ?>" class="class-image">
                            <?php endif; ?>
                            <div class="class-content">
                                <span class="class-level <?php echo strtolower($course['level']); ?>"><?php echo htmlspecialchars($course['level']); ?></span>
                                <div class="course-info">
                                    <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($course['description']); ?></p>

                                    <div class="course-meta">
                                        <small class="text-muted"><i class="fas fa-clock"></i> <?php echo $course['duration_weeks']; ?> weeks</small>
                                        <small class="text-muted"><i class="fas fa-rupee-sign"></i> ₹<?php echo number_format($course['price']); ?></small>
                                        <small class="text-muted"><i class="fas fa-user"></i> Instructor</small>
                                    </div>

                                    <div class="enrollment-info">
                                        <small><i class="fas fa-users"></i> <?php echo $course['enrolled_count']; ?>/<?php echo $course['max_students']; ?> enrolled</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if ($course['id'] > 0): ?>
                                        <?php if ($course['enrolled_count'] >= $course['max_students']): ?>
                                            <button class="btn-enroll" disabled>Course Full</button>
                                        <?php else: ?>
                                            <a href="enrollment_form.php?course_id=<?php echo $course['id']; ?>" class="btn-enroll w-100 text-center">Enroll Now</a>
                                        <?php endif; ?>
                                    <?php elseif ($course['id'] == 0): ?>
                                        <a href="enrollment_form.php" class="btn-enroll w-100 text-center">Enroll</a>
                                    <?php else: ?>
                                        <a href="login.html" class="btn-enroll w-100 text-center">Login to Enroll</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
    <script>
        // Filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const classItems = document.querySelectorAll('.class-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    const filter = this.getAttribute('data-filter');

                    classItems.forEach(item => {
                        if (filter === 'all' || item.getAttribute('data-category') === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>


