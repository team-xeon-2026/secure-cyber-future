<?php
    // Enable GZIP compression for faster delivery
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    // Start session at the beginning
    session_start();
    $session_user_id = $_SESSION['user_id'] ?? null;
    session_write_close(); // Release session lock immediately
    
    // Company Information
    $companyName = "Secure Cyber Future";
    $companyTagline = "Cyber Security";
    $foundedYear = 2015;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Cyber Future - Careers</title>
    <!-- Preconnect to CDNs for faster DNS resolution -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #1e90ff;
            --dark-bg: #0a0a20;
            --text-light: #ffffff;
            --text-muted: #b0b0cc;
        }
        
        body, html {
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
        }

        /* Navigation */
        .navbar {
            background-color: rgba(10, 10, 32, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
        }
        
        .navbar-brand span {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-link {
            color: var(--text-light) !important;
            font-weight: 500;
            margin: 0 10px;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        
        .nav-link.active::after {
            width: 100%;
        }

        /* Buttons */
        .btn-gradient {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }
        
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            transition: all 0.5s ease;
            z-index: -1;
        }
        
        .btn-gradient:hover::before {
            width: 100%;
        }
        
        .btn-gradient:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(138, 43, 226, 0.4);
        }

        /* Careers Section Styling */
        .careers-section {
            padding: 120px 0 80px;
            background: rgba(10, 10, 32, 0.9);
            position: relative;
        }

        .careers-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .careers-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }

        .careers-header p {
            color: var(--text-muted);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .job-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .job-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(138, 43, 226, 0.3);
        }

        .job-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            margin-bottom: 20px;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }

        .job-card h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .job-type {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(138, 43, 226, 0.2);
            border-radius: 20px;
            font-size: 0.85rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .job-card p {
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .job-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .job-detail-item {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .job-detail-item i {
            color: var(--primary-color);
            margin-right: 8px;
        }

        .apply-btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .apply-btn:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.4);
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            opacity: 0.6;
            filter: blur(30px);
            z-index: -1;
            animation: pulse 4s ease-in-out infinite;
        }

        .floating-element-1 {
            width: 300px;
            height: 300px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element-2 {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: 5%;
            animation-delay: 1s;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.4;
            }
            100% {
                transform: scale(1);
                opacity: 0.6;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 991px) {
            .careers-header h1 {
                font-size: 2.5rem;
            }
        }

        /* Footer Styles */
        .footer-section {
            background-color: rgba(16, 16, 45, 0.95);
            color: var(--text-light);
            position: relative;
            z-index: 1;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(138, 43, 226, 0.2);
        }
        
        .footer-brand {
            display: flex;
            align-items: center;
        }
        
        .footer-brand h3 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
        }
        
        .footer-heading {
            color: var(--text-light);
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }
        
        .footer-heading:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
        
        .footer-links, .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li, .footer-contact li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .footer-contact li {
            color: var(--text-muted);
        }
        
        .footer-contact li i {
            color: var(--primary-color);
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
        }
        
        .copyright-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
        }

        .footer-tagline {
            color: var(--text-muted);
        }
    
        @media (max-width: 480px) {
            .navbar-brand span { font-size: 1.1rem; }
            .navbar-brand img { width: 30px; height: 30px; }
        }
    </style>
</head>
<body>
    <!-- Floating Elements for premium visual effect -->
    <div class="floating-element floating-element-1"></div>
    <div class="floating-element floating-element-2"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/password.gif" alt="Secure Cyber Future" width="40" height="40">
                
                        <linearGradient id="paint0_linear" x1="20" y1="20" x2="80" y2="80" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                        <linearGradient id="paint1_linear" x1="35" y1="20" x2="65" y2="35" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                        <linearGradient id="paint2_linear" x1="35" y1="65" x2="65" y2="80" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                        <linearGradient id="paint3_linear" x1="20" y1="35" x2="35" y2="65" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                        <linearGradient id="paint4_linear" x1="65" y1="35" x2="80" y2="65" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                        <linearGradient id="paint5_linear" x1="35" y1="35" x2="65" y2="65" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#8A2BE2"/>
                            <stop offset="1" stop-color="#1E90FF"/>
                        </linearGradient>
                    </defs>
                </svg>
                <span><?php echo $companyName; ?> <small class="d-none d-md-inline"></small></span>
            </a>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">HOME</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="services.php">SERVICES</a>
                    </li>
                    <?php if ($session_user_id) { ?>
                        <li class="nav-item">
                            <a class="nav-link" href="hire.php">CAREERS</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">CONTACT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">BLOG</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                </ul>
                <?php if ($session_user_id) { ?>
                    <a href="logout.php" class="btn btn-gradient ms-lg-4">LOGOUT</a>
                <?php } else { ?>
                    <a href="login.php" class="btn btn-gradient ms-lg-4">GET LOGIN</a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <!-- Careers Section -->
    <section class="careers-section">
        <div class="container">
            <div class="careers-header" data-aos="fade-up">
                <h1>Join Our Team</h1>
                <p>Be part of a dynamic team protecting the digital world. Explore exciting career opportunities in cybersecurity.</p>
            </div>
            <div class="row">
                <!-- Job Card 1 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-user-secret"></i>
                        </div>
                        <h3>Penetration Tester</h3>
                        <span class="job-type">Full-Time</span>
                        <p>We're seeking an experienced penetration tester to identify vulnerabilities and conduct security assessments for our clients' systems and networks.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Remote/Hybrid</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>3+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Job Card 2 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Security Analyst</h3>
                        <span class="job-type">Full-Time</span>
                        <p>Join our SOC team to monitor, detect, and respond to security incidents. Protect critical infrastructure with real-time threat analysis.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>On-Site</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>2+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Job Card 3 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h3>Cloud Security Engineer</h3>
                        <span class="job-type">Full-Time</span>
                        <p>Design and implement secure cloud architectures for AWS, Azure, and GCP. Ensure compliance and protect cloud-based assets.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Remote</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>4+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Job Card 4 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-code"></i>
                        </div>
                        <h3>Application Security Developer</h3>
                        <span class="job-type">Full-Time</span>
                        <p>Build secure applications and conduct code reviews to identify security flaws. Work with development teams to implement security best practices.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Hybrid</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>3+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Job Card 5 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h3>Network Security Specialist</h3>
                        <span class="job-type">Full-Time</span>
                        <p>Manage and optimize network security infrastructure including firewalls, IDS/IPS, and VPN solutions to protect organizational assets.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>On-Site</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>3+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- Job Card 6 -->
                <div class="col-md-6 col-lg-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="job-card">
                        <div class="job-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3>Security Awareness Trainer</h3>
                        <span class="job-type">Part-Time</span>
                        <p>Develop and deliver engaging security awareness training programs. Help organizations build a strong security culture.</p>
                        <div class="job-details">
                            <div class="job-detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Remote</span>
                            </div>
                            <div class="job-detail-item">
                                <i class="fas fa-briefcase"></i>
                                <span>2+ Years Exp</span>
                            </div>
                        </div>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <a href="hire.php" class="apply-btn">Apply Now</a>
                        <?php } else { ?>
                            <a href="login.php" class="apply-btn">Apply Now</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle with Popper (deferred - non-blocking) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <!-- AOS Animation Library (deferred - non-blocking) -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
    <script>
        // Initialize AOS animations
        document.addEventListener("DOMContentLoaded", function() {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
            
            // Get all nav links
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            // Store current URL path
            const currentPath = window.location.pathname;
            const currentPage = currentPath.split('/').pop();
            
            // Loop through each link
            navLinks.forEach(link => {
                // Extract the href attribute
                const href = link.getAttribute('href');
                
                // Check if this link corresponds to the current page
                if (href === currentPage) {
                    // Add the active class
                    link.classList.add('active');
                }
                
                // Add click event listener
                link.addEventListener('click', function(e) {
                    // Remove active class from all links
                    navLinks.forEach(item => item.classList.remove('active'));
                    
                    // Add active class to the clicked link
                    this.classList.add('active');
                });
            });
        });
    </script>

    <!-- Footer Section -->
    <footer class="footer-section py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info Column -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-brand mb-4">
                        <img src="assets/password.gif" alt="Secure Cyber Future" width="50" height="50" class="me-2">
                        <h3>Secure Cyber Future</h3>
                    </div>
                    <p class="footer-tagline mb-4">Securing Future, Securing Cyber Security Solutions</p>
                    <div class="social-icons">
                        <a href="#" class="me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Company Links Column -->
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-heading mb-4">Company</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="fas fa-home me-2"></i> HOME</a></li>
                        <li><a href="services.php"><i class="fas fa-cogs me-2"></i> SERVICES</a></li>
                        <?php if (isset($_SESSION['user_id'])) { ?>
                            <li><a href="hire.php"><i class="fas fa-briefcase me-2"></i> CAREERS</a></li>
                        <?php } ?>
                        <li><a href="contact.php"><i class="fas fa-envelope me-2"></i> CONTACT</a></li>
                        <li><a href="blog.php"><i class="fas fa-blog me-2"></i> BLOG</a></li>
                        <li><a href="faq.php"><i class="fas fa-question-circle me-2"></i> FAQ</a></li>
                    </ul>
                </div>

                <!-- Services Column -->
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <h5 class="footer-heading mb-4">Services</h5>
                    <ul class="footer-links">
                        <li><a href="services.php#threat-detection"><i class="fas fa-shield-alt me-2"></i> Threat Detection</a></li>
                        <li><a href="services.php#incident-response"><i class="fas fa-exclamation-triangle me-2"></i> Incident Response</a></li>
                        <li><a href="services.php#penetration-testing"><i class="fas fa-user-secret me-2"></i> Penetration Testing</a></li>
                        <li><a href="services.php#vulnerability-assessment"><i class="fas fa-search me-2"></i> Vulnerability Assessment</a></li>
                        <li><a href="services.php#firewall-management"><i class="fas fa-fire-alt me-2"></i> Firewall Management</a></li>
                    </ul>
                </div>

                <!-- Contact Column -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading mb-4">Contact</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-phone-alt me-2"></i> 12345678900</li>
                        <li><i class="fas fa-envelope me-2"></i> testingwork102030@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Mumbai, Maharashtra, India</li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright Section -->
            <div class="copyright-section mt-5 pt-4 text-center">
                <p>© 2025 Secure Cyber Future. All Rights Reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>



