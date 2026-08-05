<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ByteRox - Cyber Security Solutions</title>
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
        
        /* Video Background - Fixed positioning to ensure visibility */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translateX(-50%) translateY(-50%);
            object-fit: cover;
        }
        
        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 32, 0.85);
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
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 100px 0;
        }
        
        .hero-badge {
            display: inline-block;
            background: rgba(138, 43, 226, 0.2);
            color: var(--primary-color);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            margin-bottom: 20px;
            border: 1px solid rgba(138, 43, 226, 0.3);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(5px);
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 30px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .hero-title .highlight {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .hero-description {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 40px;
            line-height: 1.8;
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
        
        .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-left: 15px;
            backdrop-filter: blur(5px);
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        /* 3D Device Animation */
        .device-wrapper {
            position: relative;
            perspective: 1000px;
        }
        
        .device {
            width: 100%;
            max-width: 400px;
            transform-style: preserve-3d;
            animation: float 6s ease-in-out infinite;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.5));
        }
        
        @keyframes float {
            0% {
                transform: translateY(0px) rotateY(0deg);
            }
            50% {
                transform: translateY(-20px) rotateY(10deg);
            }
            100% {
                transform: translateY(0px) rotateY(0deg);
            }
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
        
        /* About Section */
        .about-section {
            padding: 100px 0;
            position: relative;
            background: rgba(10, 10, 32, 0.7);
            backdrop-filter: blur(10px);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: -10px;
            left: 0;
        }
        
        .about-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .about-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(138, 43, 226, 0.3);
        }
        
        .about-icon {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
        }
        
        .about-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .about-card p {
            color: var(--text-muted);
            line-height: 1.7;
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .device {
                max-width: 300px;
                margin: 40px auto 0;
            }
        }
        
        @media (max-width: 767px) {
            .hero-section {
                padding: 80px 0;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .btn-outline {
                margin-left: 0;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <?php
    // Company Information
    $companyName = "ByteRox";
    $companyTagline = "Cyber Security";
    $foundedYear = 2015;
    ?>

    <!-- Video Background - Using the video from aboutus.php -->
    <div class="video-background">
        <video autoplay muted loop playsinline id="bgVideo">
            <source src="https://static.videezy.com/system/resources/previews/000/038/626/original/alb_cyber001_1080p.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>
    
    <!-- Floating Elements for premium visual effect -->
    <div class="floating-element floating-element-1"></div>
    <div class="floating-element floating-element-2"></div>
    
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
                        <a class="nav-link" href="aboutus.php">ABOUT US</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">SERVICES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">PRICING</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            PAGES
                        </a>
                        <ul class="dropdown-menu bg-dark" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item text-light" href="#">Blog</a></li>
                            <li><a class="dropdown-item text-light" href="#">FAQ</a></li>
                            <li><a class="dropdown-item text-light" href="#">Case Studies</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">CONTACT US</a>
                    </li>
                </ul>
                <a href="#" class="btn btn-gradient ms-lg-4">GET STARTED</a>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i> WELCOME TO <?php echo strtoupper($companyName); ?>
                    </div>
                    <h1 class="hero-title">Defend Your Digital World with <span class="highlight">ByteRox</span> Security Solutions</h1>
                    <p class="hero-description">We provide cutting-edge cybersecurity solutions to protect your business from evolving digital threats. Our team of experts is dedicated to ensuring your data remains secure and your operations run smoothly.</p>
                    <div class="d-flex flex-wrap">
                        <a href="#" class="btn btn-gradient">GET STARTED <i class="fas fa-arrow-right ms-2"></i></a>
                        <a href="#" class="btn btn-outline">CONTACT US</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                    <div class="device-wrapper">
                        <!-- Using the image from aboutus.php -->
                        <img src="https://cdn3d.iconscout.com/3d/premium/thumb/cyber-security-5349708-4468751.png" alt="Security Device" class="device">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <h2 class="section-title">Our Story</h2>
                    <p class="mb-4">Founded in <?php echo $foundedYear; ?>, <?php echo $companyName; ?> has been at the forefront of cybersecurity innovation, helping businesses of all sizes protect their digital assets from increasingly sophisticated threats.</p>
                    <p>Our mission is to make advanced security solutions accessible to everyone, from startups to enterprise organizations. We believe that robust cybersecurity is not a luxury—it's a necessity in today's interconnected world.</p>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-lg">
                        <!-- Using the secondary video from aboutus.php -->
                        <video autoplay muted loop playsinline class="object-fit-cover">
                            <source src="https://static.videezy.com/system/resources/previews/000/039/536/original/LOCK.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12 text-center mb-5" data-aos="fade-up" data-aos-duration="1000">
                    <h2 class="section-title mx-auto">Why Choose Us</h2>
                    <p class="mx-auto" style="max-width: 700px;">We combine cutting-edge technology with expert knowledge to deliver security solutions that protect what matters most to you.</p>
                </div>
                
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Advanced Protection</h3>
                        <p>Our multi-layered security approach ensures comprehensive protection against all types of cyber threats.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3>Expert Team</h3>
                        <p>Our security specialists have decades of combined experience in cybersecurity and threat intelligence.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-time Monitoring</h3>
                        <p>24/7 monitoring and instant alerts ensure you're always protected against emerging threats.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                    <div class="about-card">
                        <div class="about-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Customized Solutions</h3>
                        <p>We tailor our security solutions to meet your specific business needs and requirements.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Include Footer -->
    <?php include 'footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS animations
        AOS.init();
        
        // Navbar scroll effect for premium look
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.padding = '10px 0';
                navbar.style.backgroundColor = 'rgba(10, 10, 32, 0.95)';
                navbar.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.3)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.backgroundColor = 'rgba(10, 10, 32, 0.8)';
                navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.2)';
            }
        });

        // Ensure video is playing (sometimes autoplay doesn't work)
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('video');
            videos.forEach(video => {
                video.play().catch(error => {
                    console.log("Video play failed:", error);
                    // Try playing again after user interaction
                    document.body.addEventListener('click', function() {
                        video.play();
                    }, { once: true });
                });
            });
        });
    </script>
</body>
</html>