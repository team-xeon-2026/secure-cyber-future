<?php
    // Enable GZIP compression for faster delivery
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    // Start session at the beginning
    session_start();
    
    // Include database configuration file
    require_once 'config.php';
    $session_user_id = $_SESSION['user_id'] ?? null;
    session_write_close(); // Release session lock so other pages load in parallel
    
    // Company Information
    $companyName = "Secure Cyber Future";
    $companyTagline = "Cyber Security";
    $foundedYear = 2015;
    
    // Function to sanitize input data
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
    
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Secure Future - Contact Us</title>
    
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

        .contact-wrapper {
            padding: 40px 0;
            margin-top: 100px;
            position: relative;
            z-index: 1;
        }

        .contact-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contact-info {
            background: rgba(138, 43, 226, 0.1);
            padding: 40px;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Contact Info Boxes */
        .contact-info-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .contact-info-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                transparent 0%, 
                rgba(138, 43, 226, 0.1) 50%, 
                transparent 100%);
            transform: rotate(45deg);
            animation: shine 6s infinite;
        }

        .contact-info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(138, 43, 226, 0.2);
            border-color: rgba(138, 43, 226, 0.3);
        }

        @keyframes shine {
            0% { transform: rotate(45deg) translateX(-100%); }
            100% { transform: rotate(45deg) translateX(100%); }
        }

        .contact-info .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }

        .contact-info .icon i {
            color: var(--text-light);
            font-size: 24px;
        }

        .contact-info h3 {
            color: var(--text-light);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .contact-info p {
            color: var(--text-muted);
            margin-bottom: 5px;
            line-height: 1.7;
        }

        .contact-info a {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-form {
            padding: 40px;
            background: rgba(255, 255, 255, 0.02);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-light);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
            color: var(--text-light);
        }

        .form-control::placeholder {
            color: rgba(176, 176, 204, 0.7);
        }

        .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
            color: #000;
        }

        textarea.form-control {
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: var(--text-light);
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
            margin-top: 50px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(138, 43, 226, 0.4);
        }

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

        .alert {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
        }

        .copyright-section {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-muted);
        }
        
        .footer-tagline {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 500;
        }

        /* Responsive Styles */
        @media (max-width: 991px) {
            .contact-info {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            .section-title {
                font-size: 2.2rem;
            }
        }
        @media (max-width: 768px) {
            .contact-wrapper {
                margin-top: 80px;
                padding: 20px 0;
            }
            .section-title {
                font-size: 1.8rem;
            }
            .contact-info, .contact-form-container {
                padding: 25px 15px;
            }
            .floating-element-1, .floating-element-2 {
                width: 150px;
                height: 150px;
            }
        }
        @media (max-width: 480px) {
            .contact-info-item {
                padding: 15px;
            }
            .contact-info-item h4 {
                font-size: 1.1rem;
            }
            .contact-info-item p {
                font-size: 0.9rem;
            }
            .form-control {
                padding: 10px 15px;
            }
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
                    <?php if (isset($_SESSION['user_id'])) { ?>
                        <li class="nav-item">
                            <a class="nav-link" href="hire.php">CAREERS</a>
                        </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">CONTACT</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php">BLOG</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
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

    <div class="container contact-wrapper">
        <div class="floating-element floating-element-1"></div>
        <div class="floating-element floating-element-2"></div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-box row">
                    <!-- Contact Info -->
                    <div class="col-md-4 contact-info">
                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Email</h3>
                            <p>Send an email, we're always ready to assist.</p>
                            <a href="mailto:testingwork102030@gmail.com">testingwork102030@gmail.com</a>
                        </div>

                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3>Phone</h3>
                            <p>Call us now, expert help is a dial away.</p>
                            <a href="tel:+478958936">+47 895-8936</a>
                        </div>

                        <div class="contact-info-item">
                            <div class="icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <h3>Global</h3>
                            <p>Based in Detroit, ready to assist you.</p>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="col-md-8 contact-form">
                        <?php
                        $success = false;
                        $error = '';

                        if ($_SERVER["REQUEST_METHOD"] == "POST") {
                            // Sanitize input data
                            $name = sanitizeInput($_POST['name'] ?? '');
                            $email = sanitizeInput($_POST['email'] ?? '');
                            $phone = sanitizeInput($_POST['phone'] ?? '');
                            $message = sanitizeInput($_POST['message'] ?? '');
                            $companyName = sanitizeInput($_POST['companyName'] ?? '');
                            $industry = sanitizeInput($_POST['industry'] ?? '');

                            $errors = [];
                            if (empty($name)) $errors[] = "Name is required";
                            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email is required";
                            if (empty($message)) $errors[] = "Message is required";
                            
                            // Validate phone number if provided
                            if (!empty($phone) && !preg_match('/^[0-9\-\(\)\/\+\s]*$/', $phone)) {
                                $errors[] = "Invalid phone number format";
                            }
                            
                            // Validate industry selection
                            $validIndustries = ['tech', 'finance', 'healthcare', 'retail', 'other', ''];
                            if (!in_array($industry, $validIndustries)) {
                                $errors[] = "Invalid industry selection";
                            }

                            if (empty($errors)) {
                                try {
                                    // Capture additional data
                                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                                    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                                    
                                    // Store the contact form data in database
                                    $conn = getDbConnection();
                                    
                                    // Insert data into contact_forms table
                                    $sql = "INSERT INTO contact_forms (name, email, phone, message, company_name, industry, ip_address, user_agent) 
                                           VALUES (:name, :email, :phone, :message, :company_name, :industry, :ip_address, :user_agent)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':name', $name);
                                    $stmt->bindParam(':email', $email);
                                    $stmt->bindParam(':phone', $phone);
                                    $stmt->bindParam(':message', $message);
                                    $stmt->bindParam(':company_name', $companyName);
                                    $stmt->bindParam(':industry', $industry);
                                    $stmt->bindParam(':ip_address', $ipAddress);
                                    $stmt->bindParam(':user_agent', $userAgent);
                                    
                                    if ($stmt->execute()) {
                                        $success = true;
                                        
                                        // Clear form data after successful submission
                                        $name = $email = $phone = $message = $companyName = $industry = '';
                                    } else {
                                        $error = "Failed to submit your message. Please try again.";
                                    }
                                } catch (PDOException $e) {
                                    error_log("Error submitting contact form: " . $e->getMessage());
                                    $error = "An error occurred. Please try again later.";
                                }
                            } else {
                                $error = implode('<br>', $errors);
                            }
                        }
                        ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i> Thank you for your message! We'll get back to you soon.
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="contactForm">
                            <div class="form-group">
                                <label class="form-label">Enter your name</label>
                                <input type="text" class="form-control" name="name" placeholder="Enter your name" required 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" placeholder="Email" required
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" class="form-control" name="phone" placeholder="Phone"
                                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Message</label>
                                <textarea class="form-control" name="message" placeholder="Message" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="companyName" placeholder="Company Name"
                                       value="<?php echo htmlspecialchars($companyName ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">What industry are you in?</label>
                                <select class="form-select" name="industry">
                                    <option value="">Select one</option>
                                    <option value="tech" <?php if(($industry ?? '') === 'tech') echo 'selected'; ?>>Technology</option>
                                    <option value="finance" <?php if(($industry ?? '') === 'finance') echo 'selected'; ?>>Finance</option>
                                    <option value="healthcare" <?php if(($industry ?? '') === 'healthcare') echo 'selected'; ?>>Healthcare</option>
                                    <option value="retail" <?php if(($industry ?? '') === 'retail') echo 'selected'; ?>>Retail</option>
                                    <option value="other" <?php if(($industry ?? '') === 'other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-submit">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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