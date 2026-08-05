<?php
// Enable GZIP compression for faster delivery
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// Include configuration file
require_once 'config.php';
// Include reCAPTCHA helper
require_once 'recaptcha.php';

if (isset($_SESSION['user_id'])) {
    // User is already logged in, redirect to dashboard
    header('Location: index.php');
    exit();
}
// Initialize error variable
$error = '';
$debug_info = '';

// Check for Google auth error
if (isset($_SESSION['auth_error'])) {
    $error = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate form input
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Verify reCAPTCHA
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        $captcha_verified = verifyRecaptcha($recaptcha_response);
        
        if (!$captcha_verified) {
            $error = 'Please complete the CAPTCHA verification';
        } else {
            try {
                // Connect to Supabase PostgreSQL database
                $conn = getDbConnection();
                
                // Prepare and execute the query to find the user
                $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify user credentials
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful - Set consistent session variables
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['auth_provider'] = 'email';
                    $_SESSION['login_time'] = time();
                    
                    // Current timestamp for tracking login activity
                    $current_time = date('Y-m-d H:i:s');
                    
                    // Update user's login information
                    try {
                        $updateStmt = $conn->prepare("
                            UPDATE users 
                            SET 
                                last_login = :last_login,
                                login_count = COALESCE(login_count, 0) + 1,
                                auth_provider = 'email'
                            WHERE id = :id
                        ");
                        $updateStmt->bindParam(':last_login', $current_time);
                        $updateStmt->bindParam(':id', $user['id']);
                        $updateStmt->execute();
                        
                        // Record login history if table exists
                        try {
                            $loginHistoryStmt = $conn->prepare("
                                INSERT INTO login_history (user_id, login_time, ip_address, auth_provider, success, user_agent)
                                VALUES (:user_id, :login_time, :ip_address, 'email', TRUE, :user_agent)
                            ");
                            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                            $loginHistoryStmt->bindParam(':user_id', $user['id']);
                            $loginHistoryStmt->bindParam(':login_time', $current_time);
                            $loginHistoryStmt->bindParam(':ip_address', $ip);
                            $loginHistoryStmt->bindParam(':user_agent', $userAgent);
                            $loginHistoryStmt->execute();
                        } catch (Exception $e) {
                            // Login history table might not exist, continue without error
                        }
                    } catch (Exception $e) {
                        // Continue with login even if tracking fails
                    }
                    
                    // Check if password meets current security standards
                    $password_errors = validatePassword($password);
                    if (!empty($password_errors)) {
                        $_SESSION['password_update_needed'] = true;
                        $_SESSION['password_strength_message'] = 'For enhanced security, please update your password to meet our new cybersecurity standards.';
                    }
                    
                    // Redirect to dashboard
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Invalid email or password';
                    if (isset($_GET['debug'])) {
                        if (!$user) {
                            $debug_info = "User not found with email: " . $email;
                        } else {
                            $debug_info = "Password verification failed";
                        }
                    }
                }
            } catch (PDOException $e) {
                $error = "Login failed: " . $e->getMessage();
                $debug_info = "Error code: " . $e->getCode();
            }
        }
    }
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
    
    return $errors;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $config['site']['name']; ?></title>
    <!-- Preconnect to CDNs for faster DNS resolution -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google reCAPTCHA and custom script -->
    <?php outputRecaptchaScript('recaptcha-container', 'login-submit-btn', 'captcha-message'); ?>
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
            background-color: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
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
        .error-message {
            color: #ff4444;
            margin-bottom: 15px;
        }
        .alert {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
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
        
        /* Remove reCAPTCHA styling as it's now in recaptcha-style.css */
        
        /* ... remaining styles ... */
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
                        <h2 class="text-white mb-4">Login to your account</h2>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo htmlspecialchars($error); ?>
                                <?php if (!empty($debug_info) && isset($_GET['debug'])): ?>
                                    <div class="debug-info"><?php echo htmlspecialchars($debug_info); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
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
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email ?? ''); ?>" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                                <div class="text-end mt-1">
                                    <a href="forgot_password.php" class="text-white small text-decoration-none">Forgot Password?</a>
                                </div>
                            </div>
                            <!-- reCAPTCHA widget -->
                            <?php outputRecaptchaHtml('recaptcha-container', 'captcha-message'); ?>
                            <button type="submit" id="login-submit-btn" class="btn btn-primary w-100" disabled>Login</button>
                            
                            <div class="text-center text-white mb-3">
                                Don't have an account? <a href="signup.php" class="text-decoration-underline text-white">Create an account</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for password toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the eye icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });
    </script>
</body>
</html>