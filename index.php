<?php
    // Enable GZIP compression for faster delivery
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    // Start session at the beginning
    session_start();
    // Read session data then release lock so other pages can load in parallel
    $session_user_id = $_SESSION['user_id'] ?? null;
    $session_user_name = $_SESSION['user_name'] ?? null;
    $session_user_email = $_SESSION['user_email'] ?? null;
    $session_password_update_needed = $_SESSION['password_update_needed'] ?? false;
    $session_password_strength_message = $_SESSION['password_strength_message'] ?? null;
    session_write_close();

    // Debug logging function
    function debug_log($message) {
        $debug_log = __DIR__ . '/google_auth_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($debug_log, "[$timestamp][index.php] $message\n", FILE_APPEND);
    }

    // Handle Google authentication callback
    if (isset($_GET['code']) || isset($_GET['error'])) {
        // This is a Google OAuth callback
        debug_log("Google OAuth callback detected in index.php");
        debug_log("GET params: " . json_encode($_GET));
        
        require_once 'vendor/autoload.php';
        require_once 'config.php';
        
        // If there's an error from Google
        if (isset($_GET['error'])) {
            debug_log("Error from Google: " . $_GET['error']);
            $_SESSION['auth_error'] = 'Google authentication failed: ' . $_GET['error'];
            header('Location: login.php');
            exit;
        }
        
        // For code parameter, redirect to google_auth.php with all parameters
        if (isset($_GET['code'])) {
            debug_log("Code parameter found, redirecting to google_auth.php");
            $params = $_SERVER['QUERY_STRING'];
            debug_log("Redirect params: " . $params);
            header("Location: google_auth.php?" . $params);
            exit;
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Secure Future/ Home</title>
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
            padding-top:50px;
            /* overflow-x: hidden; */
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
        
        /* CSS Animated Background - No external video needed */
        .css-bg-animation {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: 
                radial-gradient(ellipse at 20% 50%, rgba(138, 43, 226, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(30, 144, 255, 0.12) 0%, transparent 60%),
                radial-gradient(ellipse at 60% 80%, rgba(138, 43, 226, 0.08) 0%, transparent 50%),
                linear-gradient(135deg, #0a0a20 0%, #0d0d2b 50%, #0a0a20 100%);
            animation: bgShift 12s ease-in-out infinite;
        }

        @keyframes bgShift {
            0%, 100% { filter: hue-rotate(0deg) brightness(1); }
            50% { filter: hue-rotate(15deg) brightness(1.05); }
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 10, 32, 0.6);
        }

        /* About section cyber animation box */
        .cyber-animation-box {
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #0a0a20, #1a0a3e);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .cyber-grid {
            position: absolute;
            width: 200%; height: 200%;
            top: -50%; left: -50%;
            background-image:
                linear-gradient(rgba(138,43,226,0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(138,43,226,0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            animation: gridMove 8s linear infinite;
        }
        @keyframes gridMove {
            0% { transform: translate(0,0); }
            100% { transform: translate(40px, 40px); }
        }
        .cyber-pulse {
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 3px solid rgba(138,43,226,0.6);
            box-shadow: 0 0 30px rgba(138,43,226,0.4), inset 0 0 30px rgba(30,144,255,0.2);
            animation: pulseRing 2s ease-in-out infinite;
            position: relative; z-index: 1;
        }
        .cyber-pulse::after {
            content: '\1F512';
            font-size: 3rem;
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
        }
        @keyframes pulseRing {
            0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 30px rgba(138,43,226,0.4); }
            50% { transform: scale(1.1); opacity: 0.8; box-shadow: 0 0 60px rgba(138,43,226,0.6), 0 0 80px rgba(30,144,255,0.3); }
        }
        .cyber-text {
            margin-top: 20px;
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(to right, #8a2be2, #1e90ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative; z-index: 1;
        }

        /* Navigation */
        .navbar {
            background-color: rgba(10, 10, 32, 0.8);
            backdrop-filter: blur(10px);
            min-height: 80px;
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
            min-height: 70vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 60px 0;
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
            margin-top: -30px;
        }
        
        .device {
            width: 100%;
            max-width: 380px;
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
            background-color: rgba(10, 10, 32, 0.9) !important; /* Semi-transparent dark background */
            color: var(--text-light) !important;
            position: relative;
            z-index: 10; /* Ensure it's above other elements */
            padding: 100px 0;
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
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
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
            margin-bottom: 20px;
        }

        .about-card h3 {
            margin-bottom: 15px;
            text-align: center;
        }

        .about-card p {
            text-align: center;
            flex-grow: 1;
        }

        .about-section .section-title {
    color: var(--text-light) !important;
}

.about-section p {
    color: var(--text-muted) !important;
}

/* Ensure no hidden content */
.container {
    position: relative;
    z-index: 20;
}
        
        /* Responsive */
        @media (max-width: 991px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .device {
                max-width: 280px;
                margin: 20px auto 0;
            }
            
            .device-wrapper {
                margin-top: 0;
            }
        }
        
        @media (max-width: 767px) {
            .hero-section {
                padding: 50px 0;
                min-height: 60vh;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .btn-outline {
                margin-left: 0;
                margin-top: 15px;
            }
        }
        
        /* Hire Services Section */
        .hire-services {
            padding: 40px 0;
            position: relative;
            background: linear-gradient(to right, #280a5f, #1e3a8a);
            overflow: hidden;
            margin-top: 30px;
        }

        .hire-services::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            opacity: 0.3;
            z-index: 1;
        }

        .hire-services .container:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #7928ca, #4361ee);
            z-index: 2;
        }

        .hire-services .container {
            position: relative;
            z-index: 2;
            padding-left: 35px;
        }

        .hire-services h2 {
            font-size: 2.8rem;
            font-weight: 700;
            line-height: 1.2;
            color: #ffffff;
            margin-bottom: 5px;
        }
        
        .hire-services p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.7) !important;
            margin-bottom: 0;
        }

        .hire-services .hire-btn {
            background: linear-gradient(to right, #7928ca, #4361ee);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(121, 40, 202, 0.4);
            border: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .hire-services .hire-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(121, 40, 202, 0.6);
        }
        
        .hire-services .hire-btn i {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        @media (max-width: 991px) {
            .hire-services {
                text-align: center;
                padding: 30px 0;
            }
            
            .hire-services h2 {
                font-size: 2.2rem;
            }
            
            .hire-services .text-lg-end {
                text-align: center !important;
                margin-top: 20px;
            }
            
            .hire-services .hire-btn {
                padding: 10px 20px;
            }
        }

        /* Security Services Section */
        .security-services {
            background-color: var(--dark-bg) !important; /* Ensure dark background */
            color: var(--text-light) !important; /* Ensure light text */
            position: relative;
            z-index: 10; /* Ensure it's above other elements */
            padding: 100px 0; /* Add more padding */
        }

        .security-services h2 {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .security-services h3 {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .security-icon-wrapper {
            width: 100px;
            height: 100px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .security-icon {
            width: 60px;
            height: 60px;
            filter: invert(56%) sepia(85%) saturate(2044%) hue-rotate(122deg) brightness(94%) contrast(101%);
        }

        
        .security-title {
            color: #00cc66;
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .security-text {
            color: var(--text-muted);
            max-width: 320px;
            margin-left: auto;
            margin-right: auto;
            font-size: 1rem;
            line-height: 1.6;
        }

        .security-services .display-4 {
            color: #00cc66 !important; /* Bright green color */
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5); /* Add text shadow for contrast */
            font-size: 2.5rem; /* Slightly smaller for better readability */
        }

        .security-services .security-title,
        .security-services .security-text {
            color: var(--text-light) !important; /* Ensure text is visible */
        }

        .btn-success {
            background-color:rgb(145, 44, 179);
            border-color:rgb(100, 28, 124);
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 204, 102, 0.3);
            padding: 10px 25px;
        }

        .btn-success:hover {
            background-color:rgb(109, 2, 136);
            border-color: rgb(109, 2, 136);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(43, 1, 60, 0.4);
        }

        .security-icon-wrapper:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color:rgb(88, 4, 94);
        }

        .text-success {
            color:rgb(112, 3, 145) !important;
        }

        @media (max-width: 767px) {
            .security-text {
                max-width: 100%;
            }
        }

        .security-services .security-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .security-services .security-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(138, 43, 226, 0.3);
        }

        .security-services .security-icon-wrapper {
            margin-bottom: 20px;
        }

        .security-services .security-title {
            margin-bottom: 15px;
            text-align: center;
        }

        .security-services .security-text {
            text-align: center;
            flex-grow: 1;
        }

        /* :root {
    --primary-bg-color: #0f1729;
    --secondary-bg-color: #0a0e1a;
    --text-color: #ffffff;
    --accent-color: #8a4fff;
    --transition-speed: 0.3s;
} */

:root {
    --dark-blue: #0a1128;
    --purple-accent: #8a4fff;
    --light-text: #ffffff;
}

.footer {
    background-color: var(--dark-blue);
    color: var(--light-text);
    padding: 50px 20px;
    max-width: 1200px;
    margin: 0 auto;
}

.logo {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}

.logo-icon {
    width: 50px;
    height: 50px;
    margin-right: 15px;
    background-color: var(--purple-accent);
    border-radius: 10px;
}

.logo-text h1 {
    font-size: 1.5rem;
    color: var(--light-text);
}

.logo-text p {
    font-size: 0.9rem;
    color: #aaa;
}

.footer-columns {
    display: flex;
    justify-content: space-between;
}

.column {
    flex: 1;
    margin-right: 20px;
}

.column h4 {
    color: var(--purple-accent);
    margin-bottom: 15px;
    text-transform: uppercase;
}

.column ul {
    list-style: none;
    padding: 0;
}

.column ul li {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
}

.column ul li a {
    color: var(--light-text);
    text-decoration: none;
    transition: color 0.3s ease;
}

.column ul li a:hover {
    color: var(--purple-accent);
}

.column ul li i {
    margin-right: 10px;
    color: var(--purple-accent);
}

.copyright {
    margin-top: 30px;
    text-align: center;
    color: #aaa;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .footer-columns {
        flex-direction: column;
    }

    .column {
        margin-right: 0;
        margin-bottom: 20px;
    }
}

.footer-tagline {
    background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 500;
}

/* Password security banner */
.password-security-banner {
    background: linear-gradient(45deg, rgba(255, 87, 34, 0.95), rgba(251, 140, 0, 0.95));
    color: white;
    padding: 15px 0;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1030;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transform: translateY(-100%);
    transition: transform 0.3s ease-in-out;
}

.password-security-banner.show {
    transform: translateY(0);
}

.password-security-banner .banner-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.password-security-banner .banner-icon {
    font-size: 24px;
    margin-right: 15px;
}

.password-security-banner .banner-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}

.password-security-banner .banner-close:hover {
    opacity: 1;
}

.password-security-banner .btn-update {
    background-color: white;
    color: #ff5722;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.2s;
    margin-left: 15px;
}

.password-security-banner .btn-update:hover {
    background-color: #f9f9f9;
    transform: translateY(-2px);
}

/* Adjust body padding when banner is shown */
body.has-security-banner {
    padding-top: 130px;
}
    </style>
</head>
<body>
    <!-- Password Security Banner -->
    <?php if ($session_password_update_needed): ?>
    <div id="passwordSecurityBanner" class="password-security-banner">
        <div class="container">
            <div class="banner-content">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shield-alt banner-icon"></i>
                    <div>
                        <strong>Security Alert:</strong> 
                        <?php echo htmlspecialchars($session_password_strength_message ?? 'For enhanced security, please update your password to meet our new cybersecurity standards.'); ?>
                    </div>
                </div>
                <div>
                    <a href="update_password.php" class="btn-update">Update Password</a>
                    <button id="dismissBanner" class="banner-close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    // Company Information
    $companyName = "Secure Cyber Future";
    // $companyTagline = "Cyber Security";
    $foundedYear = 2015;
    ?>

    <!-- Animated CSS Background (replaces slow external video for instant load) -->
    <div class="video-background">
        <div class="css-bg-animation"></div>
        <div class="video-overlay"></div>
    </div>
    
    <!-- Floating Elements for premium visual effect -->
    <div class="floating-element floating-element-1"></div>
    <div class="floating-element floating-element-2"></div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/password.gif" alt="Secure Cyber Future" width="40" height="40" loading="lazy">
                <span><?php echo $companyName; ?> <small class="d-none d-md-inline"></small></span>
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
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="hero-badge">
                        <i class="fas fa-shield-alt"></i> WELCOME TO <?php echo strtoupper($companyName); ?>
                    </div>
                    <h1 class="hero-title">Securing Future....<span class="highlight">Secure Cyber Future</span> Security Solutions</h1>
                    <p class="hero-description">Your security our commitment !</p>
                    <div class="d-flex flex-wrap">
                        <a href="services.php" class="btn btn-gradient">MORE<i class="fas fa-arrow-right ms-2"></i></a>
                      
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                    <div class="device-wrapper" style="text-align: right; padding-right: 40px;">
                        <!-- Using the image from aboutus.php -->
                        <img src="https://img.freepik.com/free-vector/cyber-security-concept_23-2148532223.jpg" alt="Cyber Security" class="device" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Hire Services Section -->
    <section class="hire-services">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-7">
                    <h2>Hire Our Services Online</h2>
                    <p>Without Upfront Payment - Flexible and Risk-Free</p>
                </div>
                
            </div>
        </div>
    </section>
    
    <!-- Security Services Section -->
    <section class="security-services">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-4 fw-bold mb-4 text-success">Taking care of your Security!</h2>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <!-- Service 1 -->
                <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up" data-aos-duration="800">
                    <div class="security-card">
                        <div class="security-icon-wrapper">
                            <img src="https://cdn-icons-png.flaticon.com/512/2885/2885417.png" alt="Services" class="security-icon">
                        </div>
                        <h3 class="security-title">Looking For Services ?</h3>
                        <p class="security-text">
                            We provide end-to-end Cyber Security Services covering the following areas: Advanced Malware Protection. Datacenter & Perimeter Security. Network Risk Assessment.
                        </p>
                    </div>
                </div>
                
                <!-- Service 2 -->
                <div class="col-md-4 mb-5 mb-md-0" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                    <div class="security-card">
                        <div class="security-icon-wrapper">
                            <img src="https://cdn-icons-png.flaticon.com/512/2885/2885424.png" alt="Support" class="security-icon">
                        </div>
                        <h3 class="security-title">Looking For Support ?</h3>
                        <p class="security-text">
                            If you're new to Secure Cyber Future, this guide answers common questions about the platform, the services we offer, and how to get started.
                        </p>
                    </div>
                </div>
                
                <!-- Service 3 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
                    <div class="security-card">
                        <div class="security-icon-wrapper">
                            <img src="https://cdn-icons-png.flaticon.com/512/2885/2885760.png" alt="Emergency" class="security-icon">
                        </div>
                        <h3 class="security-title">Emergency Response ?</h3>
                        <p class="security-text">
                            Want to secure from hackers Know you're vulnerabilities now And Fix them It fast. If you are already breached, report to us and let The Emergency Response Team to take over.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5 pt-5">
                <div class="col-12 text-center">
                    <h3 class="text-success fw-bold mb-0">We Secure, By Reporting Their Vulnerabilities!</h3>
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
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-lg about-video-placeholder">
                    <!-- Replaced slow external video with instant CSS animation -->
                    <div class="cyber-animation-box">
                        <div class="cyber-grid"></div>
                        <div class="cyber-pulse"></div>
                        <div class="cyber-text">🔒 Securing Your Future</div>
                    </div>
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
            
            // Special case for home page
            if (currentPage === '' || currentPage === '/' || currentPage === 'index.php') {
                const homeLink = document.querySelector('.nav-link[href="index.php"]');
                if (homeLink) {
                    navLinks.forEach(item => item.classList.remove('active'));
                    homeLink.classList.add('active');
                }
            }
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
                        <?php if ($session_user_id) { ?>
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
                        <li><i class="fas fa-envelope me-2"></i> testingwork102030@gmail.com</li>
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

    <!-- Add this script at the end of your file, just before the closing body tag -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password security banner functionality
        const passwordBanner = document.getElementById('passwordSecurityBanner');
        const dismissBanner = document.getElementById('dismissBanner');
        
        if (passwordBanner) {
            // Show the banner with animation
            setTimeout(function() {
                passwordBanner.classList.add('show');
                document.body.classList.add('has-security-banner');
            }, 500);
            
            // Handle dismiss button
            if (dismissBanner) {
                dismissBanner.addEventListener('click', function() {
                    passwordBanner.classList.remove('show');
                    document.body.classList.remove('has-security-banner');
                    
                    // Create a temporary dismiss for this session
                    // Note: The banner will reappear on next login until password is updated
                    fetch('dismiss_password_banner.php')
                        .then(response => response.text())
                        .catch(error => console.error('Error:', error));
                });
            }
        }
    });
    </script>
</body>
</html>
