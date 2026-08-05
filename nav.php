<?php
    // Start session at the beginning
    session_start();
    
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
    <title>Cyber Secure Future</title>
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
    
    /* User dropdown styling */
    .dropdown-menu-dark {
        background-color: rgba(15, 15, 35, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(138, 43, 226, 0.2);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        padding: 0.5rem 0;
        margin-top: 0.5rem;
    }
    
    .dropdown-item {
        color: var(--text-light);
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(90deg, rgba(138, 43, 226, 0.2), rgba(30, 144, 255, 0.2));
        color: white;
    }
    
    .dropdown-item-text {
        padding: 0.5rem 1rem;
        color: var(--text-muted);
    }
    
    .dropdown-divider {
        border-top: 1px solid rgba(138, 43, 226, 0.2);
        margin: 0.25rem 0;
    }
    
    .dropdown-menu-dark .dropdown-item.text-danger {
        color: #ff5c75;
    }
    
    .dropdown-menu-dark .dropdown-item.text-danger:hover {
        background: rgba(255, 92, 117, 0.1);
    }
</style>
</head>
<body>
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <svg width="40" height="40" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" stroke="url(#paint0_linear)" stroke-width="5"/>
                <path d="M50 20L65 35H35L50 20Z" fill="url(#paint1_linear)"/>
                <path d="M50 80L35 65H65L50 80Z" fill="url(#paint2_linear)"/>
                <path d="M20 50L35 35V65L20 50Z" fill="url(#paint3_linear)"/>
                <path d="M80 50L65 65V35L80 50Z" fill="url(#paint4_linear)"/>
                <circle cx="50" cy="50" r="15" fill="url(#paint5_linear)"/>
                <defs>
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
            <span><?php echo $companyName; ?> <small class="d-none d-md-inline"><?php echo $companyTagline; ?></small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">HOME</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="aboutus.php">SERVICES</a>
                </li>
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) { ?>
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
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) { ?>
                <div class="dropdown ms-lg-4">
                    <button class="btn btn-gradient dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="userDropdown">
                        <li><span class="dropdown-item-text text-muted small"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="update_password.php"><i class="fas fa-key me-2"></i>Update Password</a></li>
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-cog me-2"></i>Profile Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php } else { ?>
                <div class="d-flex ms-lg-4">
                    <a href="login.php" class="btn btn-gradient me-2">
                        <i class="fas fa-sign-in-alt me-1"></i> Log In
                    </a>
                    <a href="signup.php" class="btn btn-outline-light">
                        <i class="fas fa-user-plus me-1"></i> Sign Up
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>
</nav>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize AOS animations
        document.addEventListener("DOMContentLoaded", function() {
    // Get all nav links
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    // Loop through each link and add a click event listener
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remove active class from all links
            navLinks.forEach(item => item.classList.remove('active'));

            // Add active class to the clicked link
            this.classList.add('active');
        });
    });
});

    </script>
</body>
</html>  
