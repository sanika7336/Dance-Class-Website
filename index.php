<?php
/**
 * TaalBeats Homepage
 * Main website homepage with user authentication
 */

// Start session
session_start();

// Handle login success messages
$message = '';
$message_type = '';

if (isset($_GET['status']) && isset($_GET['message'])) {
    if ($_GET['status'] === 'success') {
        $message = htmlspecialchars($_GET['message']);
        $message_type = 'success';
    } elseif ($_GET['status'] === 'error') {
        $message = htmlspecialchars($_GET['message']);
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>taalbeats - Dance Academy</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    
    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        
        .navbar .nav-link,
        .navbar .dropdown-item {
            text-decoration: none !important;
        }
        .navbar .nav-link:hover,
        .navbar .dropdown-item:hover {
            text-decoration: none !important;
            background: transparent !important;
            color: inherit !important;
        }
        .profile-dropdown .dropdown-menu {
            min-width: 250px;
        }
        .profile-dropdown .dropdown-header {
            padding: 0.5rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .profile-dropdown .dropdown-item {
            padding: 0.5rem 1rem;
        }
        .profile-dropdown .dropdown-item i {
            width: 20px;
            margin-right: 10px;
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
                    <a href="index.php" class="nav-item nav-link active">Home</a>
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

    <!-- Success/Error Messages -->
    <?php if (!empty($message)): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Static Hero Section Start -->
    <div class="static-hero position-relative">
        <img src="img/home2.jpg" class="img-fluid w-100" alt="Hero Image">

        <div class="carousel-caption position-absolute bottom-0 start-0 w-100 text-center" style="padding: 60px 20px 40px 20px;">
            <div class="carousel-caption-content p-3" style="max-width: 900px; margin: 0 auto;">
                <h4 class="text-secondary text-uppercase sub-title fw-bold mb-4 fadeInUp" style="letter-spacing: 3px;"></h4>
                <h1 class="display-1 site-title text-capitalize text-white mb-4 fadeInUp"></h1>
                <p class="fs-5 text-white fadeInUp mb-4"></p>
                <div class="pt-2">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a class="btn btn-primary rounded-pill py-3 px-5 m-2 fadeInLeft neon-pulse" href="login.html">Join Now</a>
                        <a class="btn btn-outline-primary rounded-pill py-3 px-5 m-2 fadeInRight" href="register.html">Get Started</a>
                    <?php else: ?>
                        <a class="btn btn-primary rounded-pill py-3 px-5 m-2 fadeInLeft neon-pulse" href="classes.php">View courses</a>
                        <a class="btn btn-outline-primary rounded-pill py-3 px-5 m-2 fadeInRight" href="enrollment_form.php">Enroll Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Static Hero Section End -->

    <!-- About Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 wow fadeInLeft" data-wow-delay="0.1s">
                    <div class="border bg-secondary rounded">
                        <img src="img/about-1.png" class="img-fluid w-100 rounded" alt="Image">
                    </div>
                </div>
                <div class="col-lg-7 wow fadeInRight" data-wow-delay="0.3s">
                    <h4 class="text-secondary sub-title fw-bold">about The Dance School</h4>
                    <h1 class="display-3 mb-4"><strong class="site-title text-primary">taalbeats</strong>, We have been teaching dance since 2001</h1>
                    <p>  </p>
                    <p class="mb-4"> </p>
                    <a class="btn btn-primary rounded-pill text-white py-3 px-5" href="about.php">Learn More</a>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Dance Styles Section Without Carousel -->
    <div class="container-fluid training py-5">
        <div class="container py-5">
            <div class="pb-5">
                <div class="row g-4 align-items-end">
                    <div class="col-xl-8">
                        <h4 class="text-secondary sub-title fw-bold fadeInUp" style="letter-spacing: 3px;">Dance Styles</h4>
                        <h2 class="display-2 mb-0 fadeInUp">Learn A Variety of Dance Styles</h2>
                    </div>
                    <div class="col-xl-4 text-xl-end fadeInUp">
                        <a class="btn btn-primary rounded-pill text-white py-3 px-5" href="dance-style.php">View All Styles</a>
                    </div>
                </div>
            </div>

            <!-- Static Grid Instead of Carousel -->
            <div class="row g-4 pt-5">
                <!-- Card 1 -->
                <div class="col-lg-4 col-md-6 fadeInUp">
                    <div class="training-item bg-white rounded">
                        <div class="training-img position-relative rounded-top">
                            <img src="img/style1.jpg" class="img-fluid rounded-top w-100" alt="Image">
                            <h1 class="fs-1 fw-bold bg-primary text-white d-inline-block rounded p-2 position-absolute" style="top: 0; left: 0;">01</h1>
                        </div>
                        <div class="rounded-bottom border border-top-0 p-4">
                            <a href="dance-style.php" class="h4 mb-3 d-block">Classical Dance</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="dance-style.php">Read More</a>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="col-lg-4 col-md-6 fadeInUp">
                    <div class="training-item bg-white rounded">
                        <div class="training-img position-relative rounded-top">
                            <img src="img/style2.jpg" class="img-fluid rounded-top w-100" alt="Image">
                            <h1 class="fs-1 fw-bold bg-primary text-white d-inline-block rounded p-2 position-absolute" style="top: 0; left: 0;">02</h1>
                        </div>
                        <div class="rounded-bottom border border-top-0 p-4">
                            <a href="dance-style.php" class="h4 mb-3 d-block">Folk & Tribal Dance</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="dance-style.php">Read More</a>
                        </div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="col-lg-4 col-md-6 fadeInUp">
                    <div class="training-item bg-white rounded">
                        <div class="training-img position-relative rounded-top">
                            <img src="img/style3.jpg" class="img-fluid rounded-top w-100" alt="Image">
                            <h1 class="fs-1 fw-bold bg-primary text-white d-inline-block rounded p-2 position-absolute" style="top: 0; left: 0;">03</h1>
                        </div>
                        <div class="rounded-bottom border border-top-0 p-4">
                            <a href="dance-style.php" class="h4 mb-3 d-block">Contemporary</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="dance-style.php">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dance Class Start -->
    <div class="container-fluid class bg-light py-5">
        <div class="container py-5">
            <div class="pb-5">
                <h4 class="text-secondary sub-title fw-bold wow fadeInUp" data-wow-delay="0.1s">Our Dance Classes</h4>
                <h1 class="display-2 mb-0 wow fadeInUp" data-wow-delay="0.3s">Dance Classes for everyone</h1>
            </div>
            <div class="row g-4 pt-5 wow fadeInUp" data-wow-delay="0.1s">
                <div class="col-lg-4 col-md-6">
                    <div class="class-item bg-white rounded wow fadeInUp" data-wow-delay="0.1s">
                        <div class="class-img rounded-top">
                            <img src="img/beginner.jpg" class="img-fluid rounded-top w-100" alt="Image">
                        </div>
                        <div class="rounded-bottom p-4">
                            <a href="#" class="h4 mb-3 d-block">Beginner</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="gallery.php">Explore Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="class-item bg-white rounded wow fadeInUp" data-wow-delay="0.3s">
                        <div class="class-img rounded-top">
                            <img src="img/intermediate.jpg" class="img-fluid rounded-top w-100" alt="Image">
                        </div>
                        <div class="rounded-bottom p-4">
                            <a href="#" class="h4 mb-3 d-block">Intermediate</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="gallery.php">Explore Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="class-item bg-white rounded wow fadeInUp" data-wow-delay="0.5s">
                        <div class="class-img rounded-top">
                            <img src="img/advanced.jpg" class="img-fluid rounded-top w-100" alt="Image">
                        </div>
                        <div class="rounded-bottom p-4">
                            <a href="#" class="h4 mb-3 d-block">Advanced</a>
                            <a class="btn btn-primary rounded-pill text-white py-2 px-4" href="gallery.php">Explore Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Dance Class End -->

    <!-- Team Section -->
    <div class="container-fluid team py-5" style="background:#f5f5f5;">
        <div class="container py-5">
            <h2 class="text-center mb-2" style="font-family: 'Yantramanav', serif; font-size:3rem; font-weight:500; letter-spacing:1px;">Gurus</h2>
            <p class="text-center mb-5" style="font-size:1.35rem; color:#666; max-width:900px; margin:0 auto;">These are brief profiles of the Gurus who form the bedrock of TaalBeats. Their journeys and passion are a lighthouse, eternally illuminating a path towards the revival of Indian art forms.</p>
            <div class="team-carousel-wrapper position-relative">
                <button class="team-arrow left" onclick="moveTeamCarousel(-1)"><span class="fa fa-chevron-left"></span></button>
                <div class="team-carousel-view overflow-hidden">
                    <div class="team-carousel-flex d-flex transition" id="teamCarouselFlex">
                        <div class="item text-center flex-shrink-0">
                            <img src="img/team-1.jpg" class="team-circle-img mb-3" alt="Dr Neena Prasad">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Dr Neena Prasad</h4>
                            <div style="color:#888; font-size:1.1rem;">Bharatanatyam</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/team-3.jpg" class="team-circle-img mb-3" alt="Margi Vijayakumar">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Margi Vijayakumar</h4>
                            <div style="color:#888; font-size:1.1rem;">Kathak</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/meera.jpg" class="team-circle-img mb-3" alt="Geeta Chandran">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Geeta Chandran</h4>
                            <div style="color:#888; font-size:1.1rem;">Contemporary</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/male.jpg" class="team-circle-img mb-3" alt="Dr Rajashree Warrier">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Niraj Mehatar</h4>
                            <div style="color:#888; font-size:1.1rem;">Hip Hop</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/salsa.jpg" class="team-circle-img mb-3" alt="Smt. Shobana Chandrakumar">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Vijay kumar</h4>
                            <div style="color:#888; font-size:1.1rem;">Salsa</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/team-2.jpg" class="team-circle-img mb-3" alt="Guru Kelucharan Mohapatra">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Guru Kelucharan Mohapatra</h4>
                            <div style="color:#888; font-size:1.1rem;">Classical</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/male-instructor.jpg" class="team-circle-img mb-3" alt="Pt. Birju Maharaj">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Pt. sanjay pradhan</h4>
                            <div style="color:#888; font-size:1.1rem;">Bollywood</div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <img src="img/team-4.jpg" class="team-circle-img mb-3" alt="Smt. Alarmel Valli">
                            <h4 class="mb-1" style="font-family: 'Yantramanav', serif; font-size:1.5rem; font-weight:400;">Smt. Alarmel Valli</h4>
                            <div style="color:#888; font-size:1.1rem;">Contemporary</div>
                        </div>
                    </div>
                </div>
                <button class="team-arrow right" onclick="moveTeamCarousel(1)"><span class="fa fa-chevron-right"></span></button>
            </div>
            <div class="team-dots text-center mt-4" id="teamDots"></div>
        </div>
    </div>

    <!-- Dance Class Gallery Section Start -->
    <section class="dance-gallery-section py-5" style="background:#f8f9fa;">
        <div class="container">
            <h2 class="text-center mb-2" style="font-family:'Yantramanav',sans-serif;font-size:2.5rem;font-weight:700;letter-spacing:1px;">Dance Class Gallery</h2>
            <p class="text-center mb-5" style="font-size:1.2rem;color:#666;max-width:700px;margin:0 auto;">Watch Free Dance Practice Videos</p>
            <div class="video-carousel-wrapper position-relative">
                <button class="video-arrow left" onclick="moveVideoCarousel(-1)"><span class="fa fa-chevron-left"></span></button>
                <div class="video-carousel-view overflow-hidden">
                    <div class="video-carousel-flex d-flex transition" id="videoCarouselFlex">
                        <div class="item text-center flex-shrink-0">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/CNEahMJRID8" title="Bharatanatyam Video" allowfullscreen style="max-width: 100%; height: auto;"></iframe>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Kathak Video</h5>
                                    <p class="card-text">Traditional Bharatanatyam dance performance showcasing classical Indian dance forms.</p>
                                </div>
                            </div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/21Pzptbem6w" title="Dance Practice 1" allowfullscreen style="max-width: 100%; height: auto;"></iframe>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Hip Hop Practice</h5>
                                    <p class="card-text">Energetic hip hop routine for beginners.</p>
                                </div>
                            </div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/KHiMBzMYSlg" title="Dance Practice 2" allowfullscreen style="max-width: 100%; height: auto;"></iframe>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Contemporary Flow</h5>
                                    <p class="card-text">Smooth contemporary dance practice session.</p>
                                </div>
                            </div>
                        </div>
                        <div class="item text-center flex-shrink-0">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/lyo5HWEVIT0" title="Dance Practice 3" allowfullscreen style="max-width: 100%; height: auto;"></iframe>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Bollywood Beats</h5>
                                    <p class="card-text">Fun Bollywood dance practice for all levels.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="video-arrow right" onclick="moveVideoCarousel(1)"><span class="fa fa-chevron-right"></span></button>
            </div>
            <div class="video-dots text-center mt-4" id="videoDots"></div>
        </div>
    </section>
    <!-- Dance Class Gallery Section End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="js/main.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

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

    <!-- Pages Dropdown JavaScript -->
    <script>
    // Pages Dropdown Functionality (click + hover)
    function togglePagesDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const dropdownMenu = document.getElementById('pagesDropdownMenu');
        const isVisible = dropdownMenu.style.display === 'block';
        
        // Close all other dropdowns first
        closeAllDropdowns();
        
        // Toggle current dropdown
        if (isVisible) {
            dropdownMenu.style.display = 'none';
        } else {
            dropdownMenu.style.display = 'block';
        }
    }

    let pagesHoverTimeout = null;
    function openPagesOnHover() {
        clearTimeout(pagesHoverTimeout);
        document.getElementById('pagesDropdownMenu').style.display = 'block';
    }
    function keepPagesOpen() {
        clearTimeout(pagesHoverTimeout);
    }
    function closePagesOnHover() {
        pagesHoverTimeout = setTimeout(function(){
            document.getElementById('pagesDropdownMenu').style.display = 'none';
        }, 120);
    }

    function closeAllDropdowns() {
        // Close pages dropdown
        const pagesDropdown = document.getElementById('pagesDropdownMenu');
        if (pagesDropdown) {
            pagesDropdown.style.display = 'none';
        }
        // Close profile dropdown
        const profileMenu = document.getElementById('profileDropdownMenu');
        if (profileMenu) {
            profileMenu.style.display = 'none';
        }
        // Close any other Bootstrap dropdowns
        const allDropdowns = document.querySelectorAll('.dropdown-menu');
        allDropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
            dropdown.style.display = 'none';
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const pagesDropdown = document.getElementById('pagesDropdownMenu');
        const pagesToggle = document.getElementById('pagesDropdown');
        const profileDropdown = document.getElementById('profileDropdownMenu');
        const profileToggle = document.getElementById('profileDropdown');
        
        if (pagesDropdown && pagesToggle) {
            const isClickInsideDropdown = pagesDropdown.contains(event.target);
            const isClickOnToggle = pagesToggle.contains(event.target);
            
            if (!isClickInsideDropdown && !isClickOnToggle) {
                pagesDropdown.style.display = 'none';
            }
        }
        if (profileDropdown && profileToggle) {
            const isClickInside = profileDropdown.contains(event.target);
            const isClickOnToggle2 = profileToggle.contains(event.target);
            if (!isClickInside && !isClickOnToggle2) {
                profileDropdown.style.display = 'none';
            }
        }
    });

    // Close dropdown when other navbar elements are clicked
    document.addEventListener('DOMContentLoaded', function() {
        const navbarLinks = document.querySelectorAll('.navbar-nav .nav-link:not(#pagesDropdown)');
        navbarLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeAllDropdowns();
            });
        });
        
        // Handle mobile menu toggle
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                // Close pages dropdown when mobile menu is toggled
                setTimeout(() => {
                    closeAllDropdowns();
                }, 100);
            });
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        closeAllDropdowns();
    });

    // Profile Dropdown (click + hover)
    function toggleProfileDropdown(event) {
        event.preventDefault();
        event.stopPropagation();
        const menu = document.getElementById('profileDropdownMenu');
        const isVisible = menu && menu.style.display === 'block';
        closeAllDropdowns();
        if (menu) menu.style.display = isVisible ? 'none' : 'block';
    }
    let profileHoverTimeout = null;
    function openProfileOnHover() { clearTimeout(profileHoverTimeout); const m = document.getElementById('profileDropdownMenu'); if (m) m.style.display = 'block'; }
    function keepProfileOpen() { clearTimeout(profileHoverTimeout); }
    function closeProfileOnHover() { profileHoverTimeout = setTimeout(function(){ const m = document.getElementById('profileDropdownMenu'); if (m) m.style.display = 'none'; }, 120); }
    function noop(){}
    </script>

</body>
</html>
