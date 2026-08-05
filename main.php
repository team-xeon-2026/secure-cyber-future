<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SECURE CYBER FUTURE - About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS Animation Library
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"> -->
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Particles.js for animated background -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --primary: #9d4edd;
            --primary-light: #c77dff;
            --dark: #0a0118;
            --dark-light: #1a0b2e;
            --blue-accent: #3498fe;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark);
            color: white;
            overflow-x: hidden;
        }
        
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        .navbar {
            background-color: rgba(10, 1, 24, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand img {
            height: 40px;
            transition: all 0.3s ease;
        }
        
        .nav-link {
            color: white !important;
            margin: 0 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary-light) !important;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(157, 78, 221, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(157, 78, 221, 0.6);
            background: linear-gradient(45deg, var(--primary-light), var(--primary));
        }
        
        .hero-section {
            padding: 120px 0 80px;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
        }
        
        .hero-title span {
            color: var(--primary-light);
            position: relative;
            display: inline-block;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.8;
            line-height: 1.6;
        }
        
        .hero-image {
            position: relative;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
        }
        
        .hero-image:before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(157, 78, 221, 0.3) 0%, rgba(10, 1, 24, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            border-radius: 50%;
            animation: pulse 4s infinite;
        }
        
        @keyframes pulse {
            0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.5; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.8; }
            100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.5; }
        }
        
        .about-section {
            padding: 100px 0;
            background-color: var(--dark-light);
            position: relative;
            overflow: hidden;
        }
        
        .about-section:before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(157, 78, 221, 0.2) 0%, rgba(10, 1, 24, 0) 70%);
            top: -250px;
            right: -250px;
            border-radius: 50%;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            bottom: -15px;
            left: 0;
            border-radius: 2px;
        }
        
        .feature-box {
            background-color: rgba(26, 11, 46, 0.5);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            border: 1px solid rgba(157, 78, 221, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-color: rgba(157, 78, 221, 0.3);
        }
        
        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 10px 20px rgba(157, 78, 221, 0.3);
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .feature-text {
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.6;
        }
        
        .team-section {
            padding: 100px 0;
            position: relative;
        }
        
        .team-card {
            background-color: rgba(26, 11, 46, 0.5);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(157, 78, 221, 0.1);
            margin-bottom: 30px;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-color: rgba(157, 78, 221, 0.3);
        }
        
        .team-img {
            position: relative;
            overflow: hidden;
        }
        
        .team-img img {
            width: 100%;
            transition: all 0.5s ease;
        }
        
        .team-card:hover .team-img img {
            transform: scale(1.1);
        }
        
        .team-info {
            padding: 25px;
            text-align: center;
        }
        
        .team-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .team-position {
            color: var(--primary-light);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .team-social {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .team-social a {
            width: 35px;
            height: 35px;
            background-color: rgba(157, 78, 221, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
            transition: all 0.3s ease;
        }
        
        .team-social a:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .stats-section {
            padding: 80px 0;
            background-color: var(--dark-light);
            position: relative;
        }
        
        .stats-box {
            text-align: center;
            padding: 20px;
        }
        
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .stats-text {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .cta-section {
            padding: 100px 0;
            text-align: center;
            position: relative;
        }
        
        .cta-section:before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(157, 78, 221, 0.2) 0%, rgba(10, 1, 24, 0) 70%);
            bottom: -250px;
            left: -250px;
            border-radius: 50%;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-text {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: white;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(157, 78, 221, 0.3);
        }
        
        footer {
            background-color: var(--dark);
            padding: 80px 0 30px;
            position: relative;
        }
        
        .footer-logo img {
            height: 40px;
            margin-bottom: 20px;
        }
        
        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .footer-social {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .footer-social a {
            width: 40px;
            height: 40px;
            background-color: rgba(157, 78, 221, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-light);
            transition: all 0.3s ease;
        }
        
        .footer-social a:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .footer-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }
        
        .footer-title:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: linear-gradient(45deg, var(--primary), var(--primary-light));
            bottom: -10px;
            left: 0;
            border-radius: 2px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 10px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .footer-links a:hover {
            color: var(--primary-light);
            transform: translateX(5px);
        }
        
        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-contact li {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .footer-contact i {
            color: var(--primary-light);
            margin-right: 15px;
            margin-top: 5px;
        }
        
        .footer-contact span {
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Animated elements */
        .animated-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(157, 78, 221, 0.3) 0%, rgba(10, 1, 24, 0) 70%);
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
            top: 60%;
            right: 5%;
            animation-delay: 2s;
        }
        
        .circle-3 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            left: 30%;
            animation-delay: 4s;
        }
        
        /* Responsive styles */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.8rem;
            }
            
            .hero-section {
                padding: 100px 0 60px;
            }
            
            .hero-image {
                margin-top: 50px;
            }
        }
        
        @media (max-width: 767px) {
            .hero-title {
                font-size: 2.2rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .cta-title {
                font-size: 2rem;
            }
            
            .stats-number {
                font-size: 2.5rem;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: center;
            }
        }
        
        /* Glowing effect for buttons */
        .btn-glow {
            position: relative;
            overflow: hidden;
        }
        
        .btn-glow:after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.1) 50%, 
                rgba(255, 255, 255, 0) 100%);
            transform: rotate(45deg);
            animation: glowEffect 3s infinite;
        }
        
        @keyframes glowEffect {
            0% { transform: rotate(45deg) translateX(-100%); }
            100% { transform: rotate(45deg) translateX(100%); }
        }
        
        /* Digital grid lines */
        .grid-lines {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: 50px 50px;
            background-image: 
                linear-gradient(to right, rgba(157, 78, 221, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(157, 78, 221, 0.05) 1px, transparent 1px);
            z-index: 0;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div id="particles-js"></div>
    <div class="grid-lines"></div>
    
    <!-- Animated Circles -->
    <div class="animated-circle circle-1"></div>
    <div class="animated-circle circle-2"></div>
    <div class="animated-circle circle-3"></div>
    
    <div class="content-wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60" viewBox="0 0 200 60">
                        <g fill="none" fill-rule="evenodd">
                            <path fill="#3498FE" d="M30,10 C41.0457,10 50,18.9543 50,30 C50,41.0457 41.0457,50 30,50 C18.9543,50 10,41.0457 10,30 C10,18.9543 18.9543,10 30,10 Z M30,20 C24.4772,20 20,24.4772 20,30 C20,35.5228 24.4772,40 30,40 C35.5228,40 40,35.5228 40,30 C40,24.4772 35.5228,20 30,20 Z"/>
                            <path fill="#9D4EDD" d="M30,15 C38.2843,15 45,21.7157 45,30 C45,38.2843 38.2843,45 30,45 C21.7157,45 15,38.2843 15,30 C15,21.7157 21.7157,15 30,15 Z M30,25 C27.2386,25 25,27.2386 25,30 C25,32.7614 27.2386,35 30,35 C32.7614,35 35,32.7614 35,30 C35,27.2386 32.7614,25 30,25 Z"/>
                            <text font-family="Arial-BoldMT, Arial" font-size="24" font-weight="bold" fill="#FFFFFF" x="60" y="35">
                                <tspan>SECURE CYBER FUTURE</tspan>
                            </text>
                            <text font-family="Arial, Arial" font-size="12" fill="#FFFFFF" x="60" y="48">
                                <tspan>CYBER SECURITY</tspan>
                            </text>
                        </g>
                    </svg>'); ?>" alt="SECURE CYBER FUTURE Logo">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#">HOME</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">ABOUT US</a>
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
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#">Blog</a></li>
                                <li><a class="dropdown-item" href="#">FAQ</a></li>
                                <li><a class="dropdown-item" href="#">Case Studies</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">CONTACT US</a>
                        </li>
                    </ul>
                    <a href="#" class="btn btn-primary ms-lg-3 btn-glow">GET STARTED</a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                        <div class="d-flex align-items-center mb-4">
                            <i class="fas fa-shield-alt me-2" style="color: var(--primary-light);"></i>
                            <span>WELCOME TO SECURE CYBER FUTURE</span>
                        </div>
                        <h1 class="hero-title">Defend Your Digital World with <span>SECURE CYBER FUTURE</span> Security Solutions</h1>
                        <p class="hero-subtitle">We provide cutting-edge cybersecurity solutions to protect your business from evolving digital threats. Our team of experts ensures your data remains secure and your operations continue without interruption.</p>
                        <div class="d-flex flex-wrap gap-3">
                            <a href="#" class="btn btn-primary btn-glow">GET STARTED <i class="fas fa-arrow-right ms-2"></i></a>
                            <a href="#" class="btn btn-outline">CONTACT US</a>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                        <div class="hero-image">
                            <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="500" height="500" viewBox="0 0 500 500">
                                <g fill="none" fill-rule="evenodd">
                                    <rect fill="#1A0B2E" width="500" height="500" rx="20"/>
                                    <rect fill="#9D4EDD" x="100" y="100" width="300" height="500" rx="20" opacity="0.1"/>
                                    <path d="M250,100 C300,100 350,150 350,200 C350,250 300,300 250,300 C200,300 150,250 150,200 C150,150 200,100 250,100 Z" fill="#9D4EDD" opacity="0.2"/>
                                    <path d="M250,150 C275,150 300,175 300,200 C300,225 275,250 250,250 C225,250 200,225 200,200 C200,175 225,150 250,150 Z" fill="#9D4EDD" opacity="0.4"/>
                                    <rect fill="#FFFFFF" x="150" y="350" width="200" height="30" rx="5" opacity="0.1"/>
                                    <rect fill="#FFFFFF" x="150" y="400" width="200" height="30" rx="5" opacity="0.1"/>
                                    <rect fill="#9D4EDD" x="150" y="350" width="100" height="30" rx="5" opacity="0.5"/>
                                    <rect fill="#9D4EDD" x="150" y="400" width="150" height="30" rx="5" opacity="0.5"/>
                                    <circle fill="#3498FE" cx="250" cy="200" r="20" opacity="0.8"/>
                                </g>
                            </svg>'); ?>" alt="Security Illustration">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section" id="about">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right" data-aos-duration="1000">
                        <h2 class="section-title">Who We Are</h2>
                        <p class="mb-4">SECURE CYBER FUTURE is a leading cybersecurity company founded in 2015 with a mission to protect businesses of all sizes from the growing threat of cyber attacks. Our team of security experts brings decades of combined experience in threat detection, prevention, and response.</p>
                        <p class="mb-4">We believe that robust security should be accessible to everyone, which is why we've developed scalable solutions that grow with your business. Our client-first approach means we take the time to understand your unique security challenges and develop tailored strategies to address them.</p>
                        <div class="row mt-5">
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle" style="color: var(--primary-light); font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Expert Team</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle" style="color: var(--primary-light); font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">24/7 Support</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle" style="color: var(--primary-light); font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Certified Security</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle" style="color: var(--primary-light); font-size: 24px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">Affordable Plans</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                        <div class="position-relative">
                            <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="500" height="400" viewBox="0 0 500 400">
                                <g fill="none" fill-rule="evenodd">
                                    <rect fill="#1A0B2E" width="500" height="400" rx="20"/>
                                    <rect fill="#9D4EDD" x="50" y="50" width="400" height="300" rx="20" opacity="0.1"/>
                                    <circle fill="#3498FE" cx="150" cy="150" r="50" opacity="0.2"/>
                                    <circle fill="#9D4EDD" cx="350" cy="250" r="70" opacity="0.2"/>
                                    <path d="M250,100 C300,100 350,150 350,200 C350,250 300,300 250,300 C200,300 150,250 150,200 C150,150 200,100 250,100 Z" stroke="#9D4EDD" stroke-width="2" opacity="0.5" stroke-dasharray="5,5"/>
                                    <path d="M150,150 L350,250" stroke="#FFFFFF" stroke-width="1" opacity="0.3"/>
                                    <path d="M150,250 L350,150" stroke="#FFFFFF" stroke-width="1" opacity="0.3"/>
                                    <circle fill="#FFFFFF" cx="150" cy="150" r="5"/>
                                    <circle fill="#FFFFFF" cx="350" cy="250" r="5"/>
                                    <circle fill="#FFFFFF" cx="150" cy="250" r="5"/>
                                    <circle fill="#FFFFFF" cx="350" cy="150" r="5"/>
                                    <circle fill="#9D4EDD" cx="250" cy="200" r="10"/>
                                </g>
                            </svg>'); ?>" alt="About SECURE CYBER FUTURE" class="img-fluid rounded-lg shadow-lg">
                            <div class="position-absolute" style="bottom: -30px; right: -30px; animation: float 6s ease-in-out infinite;">
                                <div class="bg-gradient p-4 rounded-lg shadow-lg" style="background: linear-gradient(45deg, var(--primary), var(--primary-light));">
                                    <h3 class="text-white mb-0">7+ Years</h3>
                                    <p class="text-white mb-0">Of Excellence</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-5 py-md-7">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up" data-aos-duration="1000">
                    <h2 class="section-title text-center">Our Core Services</h2>
                    <p class="text-muted-foreground mx-auto" style="max-width: 700px;">We offer comprehensive cybersecurity solutions tailored to your business needs, ensuring your digital assets remain protected against evolving threats.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="feature-title">Threat Detection</h3>
                            <p class="feature-text">Our advanced threat detection systems identify potential security breaches before they can impact your business operations.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3 class="feature-title">Data Encryption</h3>
                            <p class="feature-text">Protect your sensitive information with our state-of-the-art encryption technologies that keep your data secure at rest and in transit.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3 class="feature-title">Identity Management</h3>
                            <p class="feature-text">Control access to your systems with our comprehensive identity and access management solutions.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <h3 class="feature-title">Secure Development</h3>
                            <p class="feature-text">Integrate security into your development lifecycle with our secure coding practices and vulnerability assessments.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="500">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-cloud"></i>
                            </div>
                            <h3 class="feature-title">Cloud Security</h3>
                            <p class="feature-text">Secure your cloud infrastructure with our specialized solutions designed for modern cloud environments.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="600">
                        <div class="feature-box">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h3 class="feature-title">Security Analytics</h3>
                            <p class="feature-text">Gain insights into your security posture with our advanced analytics and reporting tools.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up" data-aos-duration="1000">
                    <h2 class="section-title text-center">Meet Our Team</h2>
                    <p class="text-muted-foreground mx-auto" style="max-width: 700px;">Our team of cybersecurity experts brings decades of combined experience to protect your business.</p>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                        <div class="team-card">
                            <div class="team-img">
                                <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
                                    <g fill="none" fill-rule="evenodd">
                                        <rect fill="#1A0B2E" width="300" height="300"/>
                                        <circle fill="#9D4EDD" cx="150" cy="100" r="50" opacity="0.2"/>
                                        <path d="M100,150 C100,150 100,250 150,250 C200,250 200,150 200,150" stroke="#9D4EDD" stroke-width="2"/>
                                        <circle fill="#FFFFFF" cx="150" cy="100" r="40" opacity="0.1"/>
                                        <circle fill="#FFFFFF" cx="135" cy="90" r="5"/>
                                        <circle fill="#FFFFFF" cx="165" cy="90" r="5"/>
                                        <path d="M130,110 C130,110 140,120 150,120 C160,120 170,110 170,110" stroke="#FFFFFF" stroke-width="2"/>
                                    </g>
                                </svg>'); ?>" alt="Team Member">
                            </div>
                            <div class="team-info">
                                <h3 class="team-name">Alex Johnson</h3>
                                <p class="team-position">Chief Security Officer</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                        <div class="team-card">
                            <div class="team-img">
                                <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
                                    <g fill="none" fill-rule="evenodd">
                                        <rect fill="#1A0B2E" width="300" height="300"/>
                                        <circle fill="#9D4EDD" cx="150" cy="100" r="50" opacity="0.2"/>
                                        <path d="M100,150 C100,150 100,250 150,250 C200,250 200,150 200,150" stroke="#9D4EDD" stroke-width="2"/>
                                        <circle fill="#FFFFFF" cx="150" cy="100" r="40" opacity="0.1"/>
                                        <circle fill="#FFFFFF" cx="135" cy="90" r="5"/>
                                        <circle fill="#FFFFFF" cx="165" cy="90" r="5"/>
                                        <path d="M130,110 C130,110 140,120 150,120 C160,120 170,110 170,110" stroke="#FFFFFF" stroke-width="2"/>
                                    </g>
                                </svg>'); ?>" alt="Team Member">
                            </div>
                            <div class="team-info">
                                <h3 class="team-name">Sarah Chen</h3>
                                <p class="team-position">Security Analyst</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                        <div class="team-card">
                            <div class="team-img">
                                <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
                                    <g fill="none" fill-rule="evenodd">
                                        <rect fill="#1A0B2E" width="300" height="300"/>
                                        <circle fill="#9D4EDD" cx="150" cy="100" r="50" opacity="0.2"/>
                                        <path d="M100,150 C100,150 100,250 150,250 C200,250 200,150 200,150" stroke="#9D4EDD" stroke-width="2"/>
                                        <circle fill="#FFFFFF" cx="150" cy="100" r="40" opacity="0.1"/>
                                        <circle fill="#FFFFFF" cx="135" cy="90" r="5"/>
                                        <circle fill="#FFFFFF" cx="165" cy="90" r="5"/>
                                        <path d="M130,110 C130,110 140,120 150,120 C160,120 170,110 170,110" stroke="#FFFFFF" stroke-width="2"/>
                                    </g>
                                </svg>'); ?>" alt="Team Member">
                            </div>
                            <div class="team-info">
                                <h3 class="team-name">Michael Rodriguez</h3>
                                <p class="team-position">Network Security Expert</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                        <div class="team-card">
                            <div class="team-img">
                                <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
                                    <g fill="none" fill-rule="evenodd">
                                        <rect fill="#1A0B2E" width="300" height="300"/>
                                        <circle fill="#9D4EDD" cx="150" cy="100" r="50" opacity="0.2"/>
                                        <path d="M100,150 C100,150 100,250 150,250 C200,250 200,150 200,150" stroke="#9D4EDD" stroke-width="2"/>
                                        <circle fill="#FFFFFF" cx="150" cy="100" r="40" opacity="0.1"/>
                                        <circle fill="#FFFFFF" cx="135" cy="90" r="5"/>
                                        <circle fill="#FFFFFF" cx="165" cy="90" r="5"/>
                                        <path d="M130,110 C130,110 140,120 150,120 C160,120 170,110 170,110" stroke="#FFFFFF" stroke-width="2"/>
                                    </g>
                                </svg>'); ?>" alt="Team Member">
                            </div>
                            <div class="team-info">
                                <h3 class="team-name">Emily Taylor</h3>
                                <p class="team-position">Penetration Tester</p>
                                <div class="team-social">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fab fa-github"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-3 col-6" data-aos="fade-up" data-aos-duration="1000">
                        <div class="stats-box">
                            <div class="stats-number" data-count="500">0</div>
                            <div class="stats-text">Clients Protected</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="100">
                        <div class="stats-box">
                            <div class="stats-number" data-count="1500">0</div>
                            <div class="stats-text">Threats Blocked</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                        <div class="stats-box">
                            <div class="stats-number" data-count="25">0</div>
                            <div class="stats-text">Expert Team Members</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
                        <div class="stats-box">
                            <div class="stats-number" data-count="7">0</div>
                            <div class="stats-text">Years of Experience</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="container">
                <div data-aos="fade-up" data-aos-duration="1000">
                    <h2 class="cta-title">Ready to Secure Your Digital Assets?</h2>
                    <p class="cta-text">Contact our team today to learn how SECURE CYBER FUTURE can help protect your business from cyber threats with our comprehensive security solutions.</p>
                    <div class="btn-group">
                        <a href="#" class="btn btn-primary btn-glow">GET STARTED <i class="fas fa-arrow-right ms-2"></i></a>
                        <a href="#" class="btn btn-outline">LEARN MORE</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <div class="footer-logo">
                            <img src="data:image/svg+xml;base64,<?php echo base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60" viewBox="0 0 200 60">
                                <g fill="none" fill-rule="evenodd">
                                    <path fill="#3498FE" d="M30,10 C41.0457,10 50,18.9543 50,30 C50,41.0457 41.0457,50 30,50 C18.9543,50 10,41.0457 10,30 C10,18.9543 18.9543,10 30,10 Z M30,20 C24.4772,20 20,24.4772 20,30 C20,35.5228 24.4772,40 30,40 C35.5228,40 40,35.5228 40,30 C40,24.4772 35.5228,20 30,20 Z"/>
                                    <path fill="#9D4EDD" d="M30,15 C38.2843,15 45,21.7157 45,30 C45,38.2843 38.2843,45 30,45 C21.7157,45 15,38.2843 15,30 C15,21.7157 21.7157,15 30,15 Z M30,25 C27.2386,25 25,27.2386 25,30 C25,32.7614 27.2386,35 30,35 C32.7614,35 35,32.7614 35,30 C35,27.2386 32.7614,25 30,25 Z"/>
                                    <text font-family="Arial-BoldMT, Arial" font-size="24" font-weight="bold" fill="#FFFFFF" x="60" y="35">
                                        <tspan>SECURE CYBER FUTURE</tspan>
                                    </text>
                                    <text font-family="Arial, Arial" font-size="12" fill="#FFFFFF" x="60" y="48">
                                        <tspan>CYBER SECURITY</tspan>
                                    </text>
                                </g>
                            </svg>'); ?>" alt="SECURE CYBER FUTURE Logo">
                        </div>
                        <p class="footer-text">SECURE CYBER FUTURE provides cutting-edge cybersecurity solutions to protect your business from evolving digital threats. Our team of experts ensures your data remains secure.</p>
                        <div class="footer-social">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-5 mb-md-0">
                        <h4 class="footer-title">Company</h4>
                        <ul class="footer-links">
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Services</a></li>
                            <li><a href="#">Pricing</a></li>
                            <li><a href="#">Careers</a></li>
                            <li><a href="#">Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-5 mb-md-0">
                        <h4 class="footer-title">Services</h4>
                        <ul class="footer-links">
                            <li><a href="#">Threat Detection</a></li>
                            <li><a href="#">Data Encryption</a></li>
                            <li><a href="#">Identity Management</a></li>
                            <li><a href="#">Cloud Security</a></li>
                            <li><a href="#">Security Analytics</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h4 class="footer-title">Contact Us</h4>
                        <ul class="footer-contact">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Security Street, Cyber City, CS 12345</span>
                            </li>
                            <li>
                                <i class="fas fa-phone-alt"></i>
                                <span>+1 (555) 123-4567</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span>testingwork102030@gmail.com</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> Secure Cyber Future. All Rights Reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation
        AOS.init();
        
        // Particles.js configuration for animated background
        document.addEventListener('DOMContentLoaded', function() {
            particlesJS("particles-js", {
                "particles": {
                    "number": {
                        "value": 80,
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#9d4edd"
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        },
                        "polygon": {
                            "nb_sides": 5
                        }
                    },
                    "opacity": {
                        "value": 0.3,
                        "random": false,
                        "anim": {
                            "enable": false,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 3,
                        "random": true,
                        "anim": {
                            "enable": false,
                            "speed": 40,
                            "size_min": 0.1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#9d4edd",
                        "opacity": 0.2,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 2,
                        "direction": "none",
                        "random": false,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 1
                            }
                        },
                        "bubble": {
                            "distance": 400,
                            "size": 40,
                            "duration": 2,
                            "opacity": 8,
                            "speed": 3
                        },
                        "repulse": {
                            "distance": 200,
                            "duration": 0.4
                        },
                        "push": {
                            "particles_nb": 4
                        },
                        "remove": {
                            "particles_nb": 2
                        }
                    }
                },
                "retina_detect": true
            });
        });
        
        // Animate stats numbers
        document.addEventListener('DOMContentLoaded', function() {
            const statsNumbers = document.querySelectorAll('.stats-number');
            
            const animateValue = (element, start, end, duration) => {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    element.innerHTML = Math.floor(progress * (end - start) + start);
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = entry.target;
                        const count = parseInt(target.getAttribute('data-count'));
                        animateValue(target, 0, count, 2000);
                        observer.unobserve(target);
                    }
                });
            }, { threshold: 0.5 });
            
            statsNumbers.forEach(number => {
                observer.observe(number);
            });
        });
        
        // Sticky navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('shadow');
            } else {
                navbar.classList.remove('shadow');
            }
        });
    </script>
</body>
</html> 
