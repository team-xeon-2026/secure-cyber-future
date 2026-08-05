<?php
    // Enable GZIP compression for faster delivery
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    // Start session at the beginning
    require_once 'config.php';
    // session_start();
    $session_user_id = $_SESSION['user_id'] ?? null;
    session_write_close(); // Release session lock so other pages load in parallel

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
    <title>Cyber Secure Future/ Frequently Asked Questions</title>
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
        :root {
            --primary-color: #8a2be2;
            --secondary-color: #1e90ff;
            --dark-bg: #0a0a20;
            --text-light: #ffffff;
            --text-muted: #b0b0cc;
        }
        
        body, html {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            overflow-x: hidden;
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

        .faq-section {
            padding: 100px 0;
            position: relative;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            position: relative;
            display: inline-block;
            color: white;
        }

        .section-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            bottom: -15px;
            left: 0;
            border-radius: 2px;
        }

        .accordion-item {
            background-color: rgba(26, 11, 46, 0.5);
            border: 1px solid rgba(138, 43, 226, 0.1);
            margin-bottom: 15px;
            border-radius: 15px;
            overflow: hidden;
        }

        .accordion-button {
            background-color: rgba(26, 11, 46, 0.7);
            color: white;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }

        .accordion-button:not(.collapsed) {
            background-color: rgba(138, 43, 226, 0.2);
            color: var(--primary-color);
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: transparent;
        }

        .accordion-body {
            background-color: rgba(10, 1, 24, 0.5);
            color: rgba(255, 255, 255, 0.8);
            border-top: 1px solid rgba(138, 43, 226, 0.1);
        }

        .accordion-icon {
            color: var(--primary-color);
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .accordion-button:not(.collapsed) .accordion-icon {
            transform: rotate(180deg);
        }

        .grid-lines {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 50px 50px;
            background-image: 
                linear-gradient(to right, rgba(138, 43, 226, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(138, 43, 226, 0.05) 1px, transparent 1px);
            z-index: 0;
        }

        .animated-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(138, 43, 226, 0.3) 0%, rgba(10, 1, 24, 0) 70%);
            animation: pulse 8s infinite;
            z-index: 0;
        }

        .circle-1 {
            width: 300px;
            height: 300px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 200px;
            height: 200px;
            bottom: 10%;
            right: 5%;
            animation-delay: 2s;
        }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(0.8); opacity: 0.5; }
        }

        .contact-cta {
            background-color: rgba(26, 11, 46, 0.5);
            border-radius: 15px;
            padding: 40px;
            margin-top: 50px;
            text-align: center;
            border: 1px solid rgba(138, 43, 226, 0.1);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(138, 43, 226, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(138, 43, 226, 0.6);
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }

        .footer-tagline {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="grid-lines"></div>
    <div class="animated-circle circle-1"></div>
    <div class="animated-circle circle-2"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
            <img src="assets/password.gif" alt="Secure Cyber Future" width="40" height="40">
            <span><?php echo $companyName; ?> <small class="d-none d-md-inline"></small></span>
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
                        <a class="nav-link" href="services.php">SERVICES</a>
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
                        <a class="nav-link active" href="faq.php">FAQ</a>
                    </li>
                </ul>
                <?php if (isset($_SESSION['user_id'])) { ?>
                    <a href="logout.php" class="btn btn-gradient ms-lg-4">LOGOUT</a>
                <?php } else { ?>
                    <a href="login.php" class="btn btn-gradient ms-lg-4">GET LOGIN</a>
                <?php } ?>
            </div>
        </div>
    </nav>

    <section class="faq-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2 class="section-title text-center mb-5" data-aos="fade-up">Frequently Asked Questions</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <!-- Company & Culture FAQs -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="100">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    <i class="fas fa-chevron-down accordion-icon"></i>
                                    What is Cyber Secure Future?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                Cyber Secure Future is a cybersecurity firm dedicated to protecting businesses from cyber threats. We offer cutting-edge security solutions, risk assessments, and compliance services to help organizations safeguard their digital assets.
                                </div>
                            </div>
                        </div>

                        <!-- Career & Hiring FAQs -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="200">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    <i class="fas fa-chevron-down accordion-icon"></i>
                                    What services does Cyber Secure Future provide?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                We specialize in a wide range of cybersecurity solutions, including:
                                    <ul>
                                        <li>Penetration Testing (identifying vulnerabilities in networks and applications).</li>
                                        <li>Threat Intelligence & Monitoring (real-time tracking of potential threats).</li>
                                        <li>Cloud Security & Compliance (ensuring secure cloud operations).</li>
                                        <li>Incident Response & Forensics (helping organizations recover from cyberattacks).</li>
                                        <li>Cyber Awareness Training (educating teams on best security practices).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Technology & Innovation FAQs -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="300">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    <i class="fas fa-chevron-down accordion-icon"></i>
                                    How can Cyber Secure Future help prevent cyber threats?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                We implement a multi-layered security approach, combining AI-driven threat detection, continuous monitoring, and proactive risk assessments to keep organizations ahead of cybercriminals.
                                    
                                </div>  
                            </div>
                        </div>

                        <!-- Interview & Application Process FAQs -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="400">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour">
                                    <i class="fas fa-chevron-down accordion-icon"></i>
                                    What industries does Cyber Secure Future work with?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We provide cybersecurity solutions for various industries, including:
                                    <ol>
                                        <li>Finance & Banking (data protection, fraud prevention).</li>
                                        <li>Phone or Video Screening</li>
                                        <li>Technical Assessment</li>
                                        <li>On-site or Virtual Interviews</li>
                                        <li>Final Decision</li>
                                    </ol>
                                    We aim to make the process transparent and engaging for all candidates.
                                </div>
                            </div>
                        </div>

                        <!-- Remote Work & Flexibility FAQs -->
                        <div class="accordion-item" data-aos="fade-up" data-aos-delay="500">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive">
                                    <i class="fas fa-chevron-down accordion-icon"></i>
                                    Does Secure Cyber Future offer remote work options?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We offer flexible work arrangements, including:
                                    <ul>
                                        <li>Hybrid work models</li>
                                        <li>Full-time remote positions</li>
                                        <li>Office-based roles</li>
                                        <li>Flexible scheduling</li>
                                    </ul>
                                    Our priority is maintaining team collaboration while supporting individual work preferences.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact CTA -->
                    <div class="contact-cta" data-aos="fade-up" data-aos-delay="600">
                        <h3 class="mb-4">Still Have Questions?</h3>
                        <p class="mb-4">We're here to help! Reach out to our recruitment team for any additional information.</p>
                        <a href="contact.php" class="btn btn-primary">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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
                        <li><i class="fas fa-phone-alt me-2"></i> (234) 567-8912</li>
                        <li><i class="fas fa-envelope me-2"></i> info@securecyberfuture.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Kazipur 6710, Sirajganj, BD</li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright Section -->
            <div class="copyright-section mt-5 pt-4 text-center">
                <p>© 2025 Secure Cyber Future. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <style>
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
    </style>
</body>
</html>
