<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Explore Dance Styles | TaalBeats</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Yantramanav:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Ensure dance style cards are visible */
    .styles-grid {
      display: grid !important;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important;
      gap: 30px !important;
      max-width: 1200px !important;
      margin: 0 auto !important;
      padding: 0 20px !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    .style-card {
      visibility: visible !important;
      opacity: 1 !important;
      display: block !important;
      background: #ffffff !important;
      border: 2px solid #e5e7eb !important;
      border-radius: 15px !important;
      overflow: hidden !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
      position: relative !important;
      min-height: 400px !important;
      box-shadow: 0 2px 16px rgba(255,94,156,0.1) !important;
      text-decoration: none !important;
    }
    
    .style-card:hover {
      border-color: #ff5e9c !important;
      box-shadow: 0 0 20px rgba(255,94,156,0.3) !important;
      transform: translateY(-10px) scale(1.03) !important;
    }
    
    .style-card-body {
      padding: 25px !important;
      background: #ffffff !important;
      color: #181828 !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    .style-title {
      font-family: 'Yantramanav', sans-serif !important;
      font-size: 1.5rem !important;
      font-weight: 700 !important;
      color: #ff5e9c !important;
      margin-bottom: 12px !important;
      text-align: center !important;
      text-shadow: 0 0 10px rgba(255,94,156,0.3) !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    .style-desc {
      color: #6c757d !important;
      font-size: 0.95rem !important;
      line-height: 1.6 !important;
      text-align: center !important;
      margin-bottom: 20px !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    .style-img {
      width: 100% !important;
      height: 200px !important;
      object-fit: cover !important;
      transition: filter 0.3s ease !important;
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    .styles-grid-section {
      padding: 60px 0 30px 0 !important;
      background: #ffffff !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    :root {
      --main-bg: #ffffff;
      --accent: #ff5e9c;
      --accent-dark: #cc1a8a;
      --card-bg: #ffffff;
      --text-light: #181828;
      --text-muted: #6c757d;
      --glow: 0 0 20px #ff5e9c, 0 0 40px #ff5e9c44;
    }
    
    body {
      background: var(--main-bg);
      color: var(--text-light);
      font-family: 'Roboto', Arial, sans-serif;
      overflow-x: hidden;
    }
    
    .hero-section {
      width: 100%;
      min-height: 320px;
      background: url('img/dance.jpg') center/cover no-repeat;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 80px 20px 60px 20px;
      position: relative;
    }
    
    .hero-section h1 {
      font-family: 'Yantramanav', sans-serif;
      font-size: 3rem;
      color: #ffffff;
      text-shadow: 3px 3px 6px rgba(0,0,0,0.9), 0 0 30px rgba(0,0,0,0.8);
      font-weight: 700;
      margin-bottom: 12px;
      letter-spacing: 2px;
      animation: pinkPulse 2s infinite;
    }
    
    .hero-section p {
      color: #ffffff;
      font-size: 1.2rem;
      max-width: 600px;
      margin: 0 auto;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.9), 0 0 20px rgba(0,0,0,0.7);
      font-weight: 500;
    }

    .navbar .nav-link,.navbar .dropdown-item{ text-decoration:none!important }
    .navbar .nav-link:hover,.navbar .dropdown-item:hover{ text-decoration:none!important; background:transparent!important; color:inherit!important }
    .profile-dropdown .dropdown-menu{ min-width:250px }
  </style>
</head>
<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

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
                    <a href="dance-style.php" class="nav-item nav-link active">Dance style</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="pagesDropdown" onclick="togglePagesDropdown(event)" onmouseenter="openPagesOnHover()" onmouseleave="noop()">Pages</a>
                        <div class="dropdown-menu m-0" id="pagesDropdownMenu" style="display: none;" onmouseenter="keepPagesOpen()" onmouseleave="closePagesOnHover()">
                            <a href="instructors.php" class="dropdown-item">Instructors</a>
                            <a href="schedule.php" class="dropdown-item">Schedule</a>
                            <a href="classes.php" class="dropdown-item">Classes</a>
                            <a href="gallery.php" class="dropdown-item">Gallery</a>
                        </div>
                    </div>
                
                  
                    <a href="contact.php" class="nav-item nav-link">Contact Us</a>
                </div>
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
                    <a href="register.php" class="btn btn-primary rounded-pill py-2 px-4 flex-wrap flex-sm-shrink-0 neon-pulse">Register Now</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

    <!-- Hero Section -->
    <section class="hero-section">
      <h1>EXPLORE DANCE STYLES</h1>
      <p>Discover the rhythm within. Choose your dance journey and let the music guide your soul.</p>
    </section>

    <!-- Dance Styles Grid -->
    <section class="styles-grid-section">
      <div class="styles-grid">
        <!-- Bharatanatyam -->
        <div class="style-card" onclick="window.location.href='style-bharatanatyam.php'">
          <img src="img/style1.jpg" alt="Bharatanatyam" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Bharatanatyam</div>
            <div class="style-desc">Classical Indian dance form known for its grace, purity, and expressive storytelling through intricate hand gestures and footwork.</div>
          </div>
        </div>

        <!-- Kathak -->
        <div class="style-card" onclick="window.location.href='style-kathak.php'">
          <img src="img/home.jpg" alt="Kathak" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Kathak</div>
            <div class="style-desc">Ancient storytelling dance from North India, featuring fast spins, rhythmic footwork, and graceful movements that narrate epic tales.</div>
          </div>
        </div>

        <!-- Hip-Hop -->
        <div class="style-card" onclick="window.location.href='style-hiphop.php'">
          <img src="img/style3.jpg" alt="Hip-Hop" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Hip-Hop</div>
            <div class="style-desc">High-energy urban dance style with popping, locking, breaking, and freestyle moves that express attitude and creativity.</div>
          </div>
        </div>

        <!-- Contemporary -->
        <div class="style-card" onclick="window.location.href='style-contemporary.php'">
          <img src="img/style4.jpg" alt="Contemporary" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Contemporary</div>
            <div class="style-desc">Modern fusion of ballet, jazz, and lyrical dance that emphasizes emotional expression and fluid, organic movements.</div>
          </div>
        </div>

        <!-- Bollywood -->
        <div class="style-card" onclick="window.location.href='style-bollywood.php'">
          <img src="img/class-1.jpg" alt="Bollywood" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Bollywood</div>
            <div class="style-desc">Vibrant dance style inspired by Indian cinema, combining classical, folk, and modern moves with high energy and expression.</div>
          </div>
        </div>

        <!-- Salsa -->
        <div class="style-card" onclick="window.location.href='style-salsa.php'">
          <img src="img/class-2.jpg" alt="Salsa" class="style-img">
          <div class="style-card-body">
            <div class="style-title">Salsa</div>
            <div class="style-desc">Passionate Latin dance with fast footwork, spins, and partner work that celebrates rhythm, connection, and cultural heritage.</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
      <h2>Why Learn Dance With Us?</h2>
      <ul class="benefits-list">
        <li>Expert trainers with years of professional experience</li>
        <li>Personalized attention in small class sizes</li>
        <li>Modern, safe, and inspiring studio environment</li>
        <li>Opportunities to perform and compete</li>
        <li>Boost confidence, fitness, and creativity</li>
        <li>Flexible schedules for all ages and skill levels</li>
      </ul>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
      <h2>What Our Students Say</h2>
      <div class="testimonials-grid">
        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★★</div>
          <div class="testimonial-text">"The trainers are so supportive and talented! I never thought I could dance like this. The classes are fun and challenging."</div>
          <div class="testimonial-user">
            <img src="img/team-1.jpg" alt="Student" class="testimonial-user-img">
            <span class="testimonial-user-name">Priya S.</span>
          </div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★★</div>
          <div class="testimonial-text">"Joining TaalBeats was the best decision! The variety of styles and the positive vibe keep me coming back every week."</div>
          <div class="testimonial-user">
            <img src="img/team-2.jpg" alt="Student" class="testimonial-user-img">
            <span class="testimonial-user-name">Rahul M.</span>
          </div>
        </div>
        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★★</div>
          <div class="testimonial-text">"I love the energy and the friends I've made here. The trainers push you to be your best!"</div>
          <div class="testimonial-user">
            <img src="img/team-3.jpg" alt="Student" class="testimonial-user-img">
            <span class="testimonial-user-name">Emily T.</span>
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
                    <div class="d-flex justify-content-center gap-3">
                        <a class="btn btn-square btn-primary rounded-circle" href="" style="width: 45px; height: 45px; font-size: 18px;"><i class="fab fa-facebook-f text-white"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle" href="" style="width: 45px; height: 45px; font-size: 18px;"><i class="fab fa-twitter text-white"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle" href="" style="width: 45px; height: 45px; font-size: 18px;"><i class="fab fa-instagram text-white"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle" href="" style="width: 45px; height: 45px; font-size: 18px;"><i class="fab fa-youtube text-white"></i></a>
                        <a class="btn btn-square btn-primary rounded-circle" href="" style="width: 45px; height: 45px; font-size: 18px;"><i class="fab fa-tiktok text-white"></i></a>
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

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
    function togglePagesDropdown(event){event.preventDefault();event.stopPropagation();const m=document.getElementById('pagesDropdownMenu');const v=m&&m.style.display==='block';closeAllDropdowns();if(m)m.style.display=v?'none':'block';}
    let pagesHoverTimeout=null;function openPagesOnHover(){clearTimeout(pagesHoverTimeout);const m=document.getElementById('pagesDropdownMenu');if(m)m.style.display='block';}
    function keepPagesOpen(){clearTimeout(pagesHoverTimeout);}function closePagesOnHover(){pagesHoverTimeout=setTimeout(function(){const m=document.getElementById('pagesDropdownMenu');if(m)m.style.display='none';},120);}    
    function closeAllDropdowns(){const p=document.getElementById('pagesDropdownMenu');if(p)p.style.display='none';const pr=document.getElementById('profileDropdownMenu');if(pr)pr.style.display='none';document.querySelectorAll('.dropdown-menu').forEach(d=>{d.classList.remove('show');d.style.display='none';});}
    document.addEventListener('click',function(e){const pg=document.getElementById('pagesDropdownMenu');const pgt=document.getElementById('pagesDropdown');if(pg&&pgt&&!pg.contains(e.target)&&!pgt.contains(e.target))pg.style.display='none';const pr=document.getElementById('profileDropdownMenu');const prt=document.getElementById('profileDropdown');if(pr&&prt&&!pr.contains(e.target)&&!prt.contains(e.target))pr.style.display='none';});
    function toggleProfileDropdown(event){event.preventDefault();event.stopPropagation();const m=document.getElementById('profileDropdownMenu');const v=m&&m.style.display==='block';closeAllDropdowns();if(m)m.style.display=v?'none':'block';}
    let profileHoverTimeout=null;function openProfileOnHover(){clearTimeout(profileHoverTimeout);const m=document.getElementById('profileDropdownMenu');if(m)m.style.display='block';}
    function keepProfileOpen(){clearTimeout(profileHoverTimeout);}function closeProfileOnHover(){profileHoverTimeout=setTimeout(function(){const m=document.getElementById('profileDropdownMenu');if(m)m.style.display='none';},120);}function noop(){}
    </script>
</body>
</html>
