<?php
    // Enable GZIP compression for faster delivery
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ob_start('ob_gzhandler');
    } else {
        ob_start();
    }

    // No need to start session here, it will be started in config.php
    // Including config.php at the beginning
    require_once 'config.php';
    $session_user_id = $_SESSION['user_id'] ?? null;
    session_write_close(); // Release session lock so other pages load in parallel
    
    // Company Information
    $companyName = "Secure Cyber Future";
    $companyTagline = "Cyber Security";
    $foundedYear = 2015;
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // First, ensure the resume_url column exists
        try {
            $conn = getDbConnection();
            
            // Check if resume_url column exists
            $checkColumnQuery = "SELECT column_name FROM information_schema.columns 
                               WHERE table_name = 'job_applications' AND column_name = 'resume_url'";
            $stmt = $conn->query($checkColumnQuery);
            $columnExists = $stmt->rowCount() > 0;
            
            if (!$columnExists) {
                // Add resume_url column to job_applications table
                $addColumnQuery = "ALTER TABLE job_applications ADD COLUMN resume_url VARCHAR(255)";
                $conn->exec($addColumnQuery);
                
                // Update existing records to populate resume_url based on resume_path
                $updateQuery = "UPDATE job_applications 
                              SET resume_url = 'https://ohgmrgsovsgbrbyuiwfx.supabase.co/storage/v1/object/public/resumes/' || resume_path 
                              WHERE resume_path IS NOT NULL AND resume_url IS NULL";
                $conn->exec($updateQuery);
            }
        } catch (PDOException $e) {
            // Log error but continue - don't stop form processing
            error_log("Database column check error: " . $e->getMessage());
        }
        
        // Get form data
        $name = isset($_POST['name']) ? $_POST['name'] : $_SESSION['user_name'];
        $email = isset($_POST['email']) ? $_POST['email'] : $_SESSION['user_email'];
        $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
        $position = isset($_POST['position']) ? $_POST['position'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';
        
        // Validate phone (should be 10 digits)
        if (strlen(preg_replace('/[^0-9]/', '', $phone)) !== 10) {
            $error_message = "Please enter a valid 10-digit phone number";
        }
        // Validate position
        else if (empty($position)) {
            $error_message = "Please select a position";
        }
        else {
            // Handle file upload to Supabase Storage
            $resume_path = null;
            $resume_url = null;
            
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
                // Check file size (max 5MB)
                if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
                    $error_message = "File size exceeds 5MB limit";
                } else {
                    // Check file type
                    $file_ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                    $allowed_types = array('pdf', 'doc', 'docx');
                    
                    if (!in_array($file_ext, $allowed_types)) {
                        $error_message = "Only PDF, DOC, DOCX files are allowed";
                    } else {
                        // Supabase Storage API endpoint and credentials
                        $supabase_url = "https://ohgmrgsovsgbrbyuiwfx.supabase.co";
                        $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9oZ21yZ3NvdnNnYnJieXVpd2Z4Iiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MTAzMjc5OSwiZXhwIjoyMDc2NjA4Nzk5fQ.2Y4lD5rFAAhuw_GOTli9X4vOVmBI8tRcfx4fs2T2dcE";
                        $bucket_name = "resumes";
                        
                        // Sanitize and generate unique filename
                        $original_filename = basename($_FILES['resume']['name']);
                        // Remove special characters that might cause URL issues
                        $sanitized_filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '_', $original_filename);
                        $filename = uniqid() . '_' . $sanitized_filename;
                        $encoded_filename = urlencode($filename); // Encode the filename
                        
                        // Prepare file for upload
                        $file_path = $_FILES['resume']['tmp_name'];
                        
                        // Upload file to Supabase Storage
                        $ch = curl_init();
                        $storage_endpoint = "{$supabase_url}/storage/v1/object/{$bucket_name}/{$encoded_filename}";
                        
                        // Log the request for debugging
                        error_log("Uploading file to: " . $storage_endpoint);
                        error_log("File size: " . filesize($file_path) . " bytes");
                        
                        // Set up cURL options for file upload
                        curl_setopt_array($ch, [
                            CURLOPT_URL => $storage_endpoint,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => file_get_contents($file_path),
                            CURLOPT_HTTPHEADER => [
                                "Authorization: Bearer {$supabase_key}",
                                "Content-Type: application/octet-stream",
                                "x-upsert: true"
                            ],
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_VERBOSE => true
                        ]);
                        
                        // Create a file handle for the verbose output
                        $verbose_output = fopen('php://temp', 'w+');
                        curl_setopt($ch, CURLOPT_STDERR, $verbose_output);
                        
                        // Execute the cURL request
                        $response = curl_exec($ch);
                        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        
                        // Log request details
                        rewind($verbose_output);
                        $verbose_log = stream_get_contents($verbose_output);
                        fclose($verbose_output);
                        
                        if (curl_errno($ch)) {
                            // Handle cURL error
                            $curl_error = curl_error($ch);
                            error_log("cURL Error: " . $curl_error);
                            error_log("cURL Verbose Log: " . $verbose_log);
                            $error_message = "Error uploading file: " . $curl_error;
                        } elseif ($http_code >= 400) {
                            // Handle HTTP error
                            error_log("HTTP Error: " . $http_code);
                            error_log("Response: " . $response);
                            error_log("cURL Verbose Log: " . $verbose_log);
                            $error_message = "Error uploading file. HTTP code: " . $http_code . ". Response: " . $response;
                        } else {
                            // Success
                            $resume_path = "{$bucket_name}/{$filename}";
                            $resume_url = "{$supabase_url}/storage/v1/object/public/{$bucket_name}/{$encoded_filename}";
                            error_log("File uploaded successfully to: " . $resume_url);
                        }
                        
                        curl_close($ch);
                    }
                }
            }
            
            // If no errors, save to Supabase database
            if (!isset($error_message)) {
                try {
                    // Get database connection
                    $conn = getDbConnection();
                    
                    // Insert record into job_applications table
                    $stmt = $conn->prepare("
                        INSERT INTO job_applications (user_id, name, email, phone, position, message, resume_path, resume_url, created_at) 
                        VALUES (:user_id, :name, :email, :phone, :position, :message, :resume_path, :resume_url, NOW())
                    ");
                    
                    // Bind parameters
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':position', $position);
                    $stmt->bindParam(':message', $message);
                    $stmt->bindParam(':resume_path', $resume_path);
                    $stmt->bindParam(':resume_url', $resume_url);
                    
                    // Execute statement
                    $stmt->execute();
                    
                    // Set success message in session
                    $_SESSION['application_success'] = true;
                    
                    // Redirect to avoid form resubmission
                    header("Location: hire.php");
                    exit;
                    
                } catch (PDOException $e) {
                    $error_message = "Database error: " . $e->getMessage();
                }
            }
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyber Secure Future/ Hire Us</title>
    
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
            padding-top: 80px; /* Added padding for fixed navbar */
        }

        /* Video Background */
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

        /* Hiring Section Styles */
        .hiring-section {
            padding: 80px 0;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hiring-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMSIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjEpIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            opacity: 0.1;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            opacity: 0.4;
            filter: blur(70px);
            z-index: -1;
        }

        .floating-element-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            left: 5%;
        }

        .floating-element-2 {
            width: 250px;
            height: 250px;
            bottom: 10%;
            right: 5%;
        }

        .hiring-form-container {
            background: rgba(15, 15, 40, 0.7);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(138, 43, 226, 0.2);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .hiring-form-container:hover {
            box-shadow: 0 20px 40px rgba(138, 43, 226, 0.3);
            border-color: rgba(138, 43, 226, 0.4);
            transform: translateY(-5px);
        }

        .form-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        .form-title::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-label {
            color: var(--text-light);
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-text {
            color: rgba(255, 255, 255, 0.6);
        }

        /* Custom Dropdown Styling */
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, rgba(138, 43, 226, 0.8) 50%),
                              linear-gradient(135deg, rgba(138, 43, 226, 0.8) 50%, transparent 50%);
            background-position: calc(100% - 20px) center, calc(100% - 15px) center;
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
            padding-right: 40px; /* Space for the custom arrow */
            cursor: pointer;
            opacity: 1; /* Ensure it's visible */
            position: relative; /* Don't position it absolutely */
            width: 100%; /* Full width */
            height: auto; /* Auto height */
            overflow: visible; /* Allow dropdown to show */
            clip: auto; /* Don't clip */
            white-space: normal; /* Allow text to wrap */
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        select.form-control:focus {
            background-image: linear-gradient(45deg, transparent 50%, rgba(30, 144, 255, 0.8) 50%),
                              linear-gradient(135deg, rgba(30, 144, 255, 0.8) 50%, transparent 50%);
            background-color: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        
        select.form-control option {
            background-color: rgba(15, 15, 40, 0.95);
            color: white;
            padding: 12px;
            font-size: 14px;
        }

        /* Pulse animation for dropdown */
        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(138, 43, 226, 0.4);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(138, 43, 226, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(138, 43, 226, 0);
            }
        }

        /* Remove custom dropdown container styles that conflict with native select */
        .select-dropdown-container {
            display: none !important;
        }
        
        /* Hide native select completely but keep it accessible */
        .select-dropdown-container select {
            display: none !important;
        }
        
        /* Position the custom dropdown properly */
        .custom-options-container {
            display: none !important;
        }
        
        /* Remove any select2 or other frameworks that might interfere */
        .select2-container, .select2-dropdown, .select2-search, .select2-results {
            display: none !important;
        }
        
        /* Custom select appearance - hide all of these */
        .custom-select-trigger {
            display: none !important;
        }
        
        .custom-options-container {
            display: none !important;
        }

        .btn-submit {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
            box-shadow: 0 10px 20px rgba(138, 43, 226, 0.3);
            width: 100%;
            margin-top: 20px;
            font-size: 1.1rem;
        }

        .btn-submit::before {
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

        .btn-submit:hover::before {
            width: 100%;
        }

        .btn-submit:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(138, 43, 226, 0.4);
        }

        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px;
            border: none;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-left: 4px solid #28a745;
            color: #d4edda;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
            color: #f8d7da;
        }

        .is-valid {
            border-color: #28a745 !important;
            background-image: none !important;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important;
        }

        @media (max-width: 768px) {
            .hiring-form-container {
                padding: 25px 15px;
            }
            .form-title {
                font-size: 2rem;
            }
            .hero-section {
                padding: 100px 0 50px;
            }
        }
        
        @media (max-width: 480px) {
            .hiring-form-container {
                padding: 20px 10px;
            }
            .form-title {
                font-size: 1.5rem;
            }
            .form-control {
                padding: 10px;
            }
            .btn-submit {
                padding: 12px 20px;
            }
        }

        /* Success message styling */
        .success-message {
            background-color: rgba(38, 222, 129, 0.1);
            border-left: 4px solid #26de81;
            color: #26de81;
            padding: 16px;
            margin: 20px 0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .success-message i {
            font-size: 24px;
            margin-right: 12px;
        }
        
        .success-message p {
            margin: 0;
            font-weight: 500;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
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
                <a class="nav-link active" href="hire.php">CAREERS</a>
                </li>
            <?php }?>
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
            <?php if (isset($_SESSION['user_id'])) { ?>
                <a href="logout.php" class="btn btn-gradient ms-lg-4">LOGOUT</a>
            <?php } else { ?>
                <a href="login.php" class="btn btn-gradient ms-lg-4">GET LOGIN</a>
            <?php } ?>
        </div>
    </div>
</nav>

<!-- Floating Elements -->
<div class="floating-element floating-element-1"></div>
<div class="floating-element floating-element-2"></div>

    <section class="hiring-section">
        <div class="container">
            <?php
            if (isset($_SESSION['application_success']) && $_SESSION['application_success'] === true) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Your application has been submitted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
                unset($_SESSION['application_success']);
            }

            if (isset($error_message)) {
            echo '<div class="alert alert-danger" role="alert"><i class="fas fa-exclamation-circle me-2"></i> ' . htmlspecialchars($error_message) . '</div>';
            }
            ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="hiring-form-container" data-aos="fade-up">
                        <h2 class="form-title mb-4">Join Secure Cyber Future</h2>
                    
                        <form id="hiringForm" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="Name" class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                                    <input type="text" class="form-control" id="Name" name="name" required readonly value="<?= htmlspecialchars($_SESSION['user_name']); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required readonly value="<?= htmlspecialchars($_SESSION['user_email']); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label"><i class="fas fa-phone-alt me-2"></i>Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10}" title="Please enter exactly 10 digits" maxlength="10">
                                <small class="form-text">Enter a 10-digit phone number without spaces or special characters.</small>
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label"><i class="fas fa-briefcase me-2"></i>Position of Interest</label>
                                <select class="form-control" id="position" name="position" required>
                                    <option value="">Select a Position</option>
                                    <option value="software">Software Engineering</option>
                                    <option value="security">Security Operations</option>
                                    <option value="compliance">Compliance & Risk</option>
                                    <option value="cloud">Cloud Infrastructure</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="resume" class="form-label"><i class="fas fa-file-pdf me-2"></i>Upload Resume</label>
                                <input type="file" class="form-control" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <small class="form-text">Accepted formats: PDF, DOC, DOCX (Max size: 5MB)</small>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label"><i class="fas fa-comment-alt me-2"></i>Additional Information</label>
                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Tell us why you'd be a great fit for Secure Cyber Future"></textarea>
                            </div>
                            <button type="submit" class="btn-submit"><i class="fas fa-paper-plane me-2"></i>Submit Application</button>
                        </form>
                </div>
                </div>
            </div>
        </div>
    </section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
    
    // Phone number validation
    document.getElementById('phone').addEventListener('input', function(e) {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Validate the phone number
        if (this.value.length === 10) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
            if (this.value.length > 0) {
                this.classList.add('is-invalid');
            }
        }
    });

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

        .footer-tagline {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 500;
        }
    </style>
</body>
</html>
