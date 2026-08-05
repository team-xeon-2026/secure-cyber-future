<?php
// Enable GZIP compression for faster delivery
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// Include configuration file
require_once 'config.php';

// Initialize variables for form handling
$registration_success = false;
$error_message = '';
$debug_info = '';  // For debugging

// Check for Google auth error
if (isset($_SESSION['auth_error'])) {
    $error_message = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate form input
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email format';
    } else {
        // Enhanced password validation for cybersecurity website
        $password_errors = validatePassword($password);
        
        if (!empty($password_errors)) {
            $error_message = 'Password does not meet security requirements: ' . implode(', ', $password_errors);
        } else {
            // Connect to Supabase PostgreSQL database
            try {
                $conn = getDbConnection();
                
                // Check if the users table exists, create it if it doesn't
                $checkTableSql = "SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = 'users'
                )";
                $tableExists = $conn->query($checkTableSql)->fetchColumn();
                
                if (!$tableExists) {
                    // Create the users table
                    $createTableSql = "
                    CREATE TABLE IF NOT EXISTS users (
                        id UUID PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE,
                        password VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
                    )";
                    $conn->exec($createTableSql);
                    
                    // Add index for faster email lookups
                    $createIndexSql = "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)";
                    $conn->exec($createIndexSql);
                }
                
                // Check if email already exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
                $checkStmt->bindParam(':email', $email);
                $checkStmt->execute();
                
                if ($checkStmt->fetchColumn() > 0) {
                    $error_message = 'Email already registered. Please use a different email or try to login.';
                } else {
                    // Hash the password for security
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Generate a UUID for the user
                    $uuid = generateUUID();
                    
                    // Current timestamp for tracking
                    $current_time = date('Y-m-d H:i:s');
                    
                    // Prepare and execute the query to insert the new user with named parameters
                    $stmt = $conn->prepare("
                        INSERT INTO users (
                            id, 
                            name, 
                            email, 
                            password,
                            auth_provider,
                            created_at,
                            updated_at,
                            last_login,
                            login_count
                        ) 
                        VALUES (
                            :id, 
                            :name, 
                            :email, 
                            :password,
                            'email',
                            :created_at,
                            :updated_at,
                            :last_login,
                            1
                        )
                    ");
                    
                    // Bind parameters
                    $stmt->bindParam(':id', $uuid);
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':created_at', $current_time);
                    $stmt->bindParam(':updated_at', $current_time);
                    $stmt->bindParam(':last_login', $current_time);
                    
                    // Execute the query
                    $result = $stmt->execute();
                    
                    if ($result) {
                        // Record signup event in signup_events table if it exists
                        try {
                            $signupEventStmt = $conn->prepare("
                                INSERT INTO signup_events (user_id, signup_time, ip_address, auth_provider)
                                VALUES (:user_id, :signup_time, :ip_address, 'email')
                            ");
                            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                            $signupEventStmt->bindParam(':user_id', $uuid);
                            $signupEventStmt->bindParam(':signup_time', $current_time);
                            $signupEventStmt->bindParam(':ip_address', $ip);
                            $signupEventStmt->execute();
                        } catch (Exception $e) {
                            // Signup events table might not exist, continue without error
                        }
                        
                        // Registration successful
                        $registration_success = true;
                        $error_message = "Failed to insert user record";
                    }
                }
            } catch (PDOException $e) {
                $error_message = "Registration failed. Please try again later.";
            }
        }
    }
}

/**
 * Generate a UUID v4
 * @return string UUID
 */
function generateUUID() {
    // Generate 16 bytes (128 bits) of random data
    $data = random_bytes(16);
    
    // Set version to 4 (random)
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
    // Output the 36 character UUID
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

/**
 * Validate password strength
 * @param string $password The password to validate
 * @return array Array of error messages, empty if password is valid
 */
function validatePassword($password) {
    $errors = [];
    
    // Check minimum length (8 characters for cybersecurity best practices)
    if (strlen($password) < 8) {
        $errors[] = 'password must be at least 8 characters long';
    }
    
    // Check for uppercase letters
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'password must include at least one uppercase letter';
    }
    
    // Check for lowercase letters
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'password must include at least one lowercase letter';
    }
    
    // Check for numbers
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'password must include at least one number';
    }
    
    // Check for special characters
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'password must include at least one special character';
    }
    
    // Check for common patterns
    $common_patterns = [
        'password', '123456', 'qwerty', 'abc123', 'admin', 'welcome', 
        'login', 'qwerty123', '12345678', '111111', '1234567890'
    ];
    
    $lowercase_password = strtolower($password);
    foreach ($common_patterns as $pattern) {
        if (strpos($lowercase_password, $pattern) !== false) {
            $errors[] = 'password contains a common pattern that is easily guessable';
            break;
        }
    }
    
    return $errors;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - <?php echo $config['site']['name']; ?></title>
    <!-- Preconnect to CDNs for faster DNS resolution -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
        body {
            background-color: #121212;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            position: relative;
            overflow: hidden;
        }
        #video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: -1;
        }
        .login-container {
            background-color: #1E1E1E;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .background-image {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6));
            background-size: cover;
            background-position: center;
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 400px;
        }
        .lock-image {
            width: min(95%, 400px);
            height: auto;
            margin: 20px 0;
            object-fit: contain;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        @media (max-width: 768px) {
            .lock-image {
                width: min(90%, 350px);
                margin: 15px 0;
            }
        }
        @media (max-width: 480px) {
            .background-image {
                min-height: 250px;
                padding: 15px;
            }
            .lock-image {
                width: 200px;
            }
            .login-body {
                padding: 20px;
            }
        }
        .form-control {
            background-color: rgba(255,255,255,0.1);
            border: none;
            color: white;
        }
        .form-control:focus {
            background-color: rgba(255,255,255,0.2);
            color: white;
            box-shadow: none;
            border-color: #6a11cb;
        }
        .btn-primary {
            background-color: #6a11cb;
            border: none;
        }
        .btn-primary:hover {
            background-color: #5d0fa3;
        }
        .social-login .btn {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .social-login .btn:hover {
            background-color: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .btn-google {
            background-color: rgba(219, 68, 55, 0.2) !important;
        }
        .btn-google:hover {
            background-color: rgba(219, 68, 55, 0.3) !important;
        }
        .or-divider {
            display: flex;
            align-items: center;
            color: white;
            margin: 20px 0;
        }
        .or-divider::before, .or-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .or-divider::before {
            margin-right: 10px;
        }
        .or-divider::after {
            margin-left: 10px;
        }
        .alert {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
        }
        .debug-info {
            font-size: 0.8rem;
            color: #ff9800;
            margin-top: 10px;
            padding: 5px;
            background-color: rgba(0,0,0,0.2);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <video id="video-background" autoplay muted loop>
        <source src="log.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <div class="login-container row g-0">
                    <div class="col-md-5 background-image d-none d-md-flex">
                        <img src="lock.gif" alt="Security Lock" class="lock-image">
                        <div class="text-center">
                            <h2><?php echo $config['site']['name']; ?></h2>
                            <p>Securing Your Digital Future</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4 p-md-5">
                        <h2 class="text-white mb-4">Create an account</h2>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($registration_success): ?>
                            <div class="alert alert-success">
                                Registration successful! <a href="login.php" class="text-decoration-underline text-white">Login to your account</a>
                            </div>
                        <?php else: ?>
                            <!-- Social Login Options -->
                            <div class="social-login d-flex gap-3 justify-content-center mb-4">
                                <a href="google_auth.php?login=google" class="btn btn-google d-flex align-items-center gap-2 justify-content-center w-100 py-2">
                                    <i class="fab fa-google"></i>
                                    Continue with Google
                                </a>
                            </div>
                            
                            <div class="or-divider">OR</div>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <input type="text" name="name" class="form-control" placeholder="Enter Your Name" required>
                                </div>
                                <div class="mb-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-3 position-relative">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;" id="togglePassword">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                    
                                    <!-- Password strength meter -->
                                    <div class="password-strength mt-2" style="display: none;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-white small">Password Strength:</span>
                                            <span class="text-white small" id="strength-text">Too Weak</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div id="strength-meter" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Password requirements -->
                                    <div class="password-requirements mt-2 small text-white-50">
                                        <p class="mb-1">Password must:</p>
                                        <ul class="ps-3 mb-0" style="list-style-type: none;">
                                            <li id="length-check"><i class="fa fa-circle-xmark text-danger"></i> Be at least 8 characters long</li>
                                            <li id="uppercase-check"><i class="fa fa-circle-xmark text-danger"></i> Include at least one uppercase letter</li>
                                            <li id="lowercase-check"><i class="fa fa-circle-xmark text-danger"></i> Include at least one lowercase letter</li>
                                            <li id="number-check"><i class="fa fa-circle-xmark text-danger"></i> Include at least one number</li>
                                            <li id="special-check"><i class="fa fa-circle-xmark text-danger"></i> Include at least one special character</li>
                                            <li id="pattern-check"><i class="fa fa-circle-xmark text-danger"></i> Not contain common patterns</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="termsCheck" required>
                                    <label class="form-check-label text-white" for="termsCheck">
                                        I agree to the Terms & Conditions
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-3">Create account</button>
                                
                                <div class="text-center text-white mb-3">
                                    Already have an account? <a href="login.php" class="text-decoration-underline text-white">Log in</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript for password toggle and strength meter -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        // Password strength checker
        const strengthMeter = document.getElementById('strength-meter');
        const strengthText = document.getElementById('strength-text');
        const passwordStrength = document.querySelector('.password-strength');
        
        // Password requirement checkers
        const lengthCheck = document.getElementById('length-check');
        const uppercaseCheck = document.getElementById('uppercase-check');
        const lowercaseCheck = document.getElementById('lowercase-check');
        const numberCheck = document.getElementById('number-check');
        const specialCheck = document.getElementById('special-check');
        const patternCheck = document.getElementById('pattern-check');
        
        // Common patterns to avoid
        const commonPatterns = [
            'password', '123456', 'qwerty', 'abc123', 'admin', 'welcome', 
            'login', 'qwerty123', '12345678', '111111', '1234567890'
        ];
        
        if (password) {
            password.addEventListener('input', function() {
                const val = this.value;
                let score = 0;
                
                // Show password strength meter once user starts typing
                if (val.length > 0) {
                    passwordStrength.style.display = 'block';
                } else {
                    passwordStrength.style.display = 'none';
                }
                
                // Check length
                const hasLength = val.length >= 8;
                updateCheckIcon(lengthCheck, hasLength);
                if (hasLength) score += 20;
                
                // Check uppercase
                const hasUppercase = /[A-Z]/.test(val);
                updateCheckIcon(uppercaseCheck, hasUppercase);
                if (hasUppercase) score += 20;
                
                // Check lowercase
                const hasLowercase = /[a-z]/.test(val);
                updateCheckIcon(lowercaseCheck, hasLowercase);
                if (hasLowercase) score += 20;
                
                // Check numbers
                const hasNumber = /[0-9]/.test(val);
                updateCheckIcon(numberCheck, hasNumber);
                if (hasNumber) score += 20;
                
                // Check special chars
                const hasSpecial = /[^A-Za-z0-9]/.test(val);
                updateCheckIcon(specialCheck, hasSpecial);
                if (hasSpecial) score += 20;
                
                // Check for common patterns
                let hasCommonPattern = false;
                const lowercaseVal = val.toLowerCase();
                for (const pattern of commonPatterns) {
                    if (lowercaseVal.includes(pattern)) {
                        hasCommonPattern = true;
                        break;
                    }
                }
                updateCheckIcon(patternCheck, !hasCommonPattern);
                if (hasCommonPattern) score = Math.max(0, score - 30); // Heavily penalize common patterns
                
                // Update the strength meter
                strengthMeter.style.width = `${score}%`;
                
                // Update strength text and color
                if (score <= 20) {
                    strengthText.textContent = 'Too Weak';
                    strengthMeter.className = 'progress-bar bg-danger';
                } else if (score <= 40) {
                    strengthText.textContent = 'Weak';
                    strengthMeter.className = 'progress-bar bg-warning';
                } else if (score <= 60) {
                    strengthText.textContent = 'Fair';
                    strengthMeter.className = 'progress-bar bg-info';
                } else if (score <= 80) {
                    strengthText.textContent = 'Good';
                    strengthMeter.className = 'progress-bar bg-primary';
                } else {
                    strengthText.textContent = 'Strong';
                    strengthMeter.className = 'progress-bar bg-success';
                }
            });
        }
        
        // Helper function to update check icons
        function updateCheckIcon(element, isValid) {
            if (!element) return;
            
            const icon = element.querySelector('i');
            if (isValid) {
                icon.classList.remove('fa-circle-xmark', 'text-danger');
                icon.classList.add('fa-circle-check', 'text-success');
            } else {
                icon.classList.remove('fa-circle-check', 'text-success');
                icon.classList.add('fa-circle-xmark', 'text-danger');
            }
        }
    });
    </script>
</body>
</html>