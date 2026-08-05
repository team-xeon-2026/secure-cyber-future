<?php
session_start();

// Include configuration file
require_once 'config.php';

// Initialize error variable
$error = '';
$debug_info = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate form input
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
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
                // Login successful
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];  // This is now a UUID
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $config['site']['name']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            background-color: #1E1E1E;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
        }
        .background-image {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23purple" fill-opacity="0.3" d="M0,256L48,250.7C96,245,192,235,288,208C384,181,480,139,576,138.7C672,139,768,181,864,197.3C960,213,1056,203,1152,170.7C1248,139,1344,85,1392,58.7L1440,32L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0L192,0L96,0L0,0Z"></path></svg>'),
                        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23purple" fill-opacity="0.3" d="M0,256L48,250.7C96,245,192,235,288,208C384,181,480,139,576,138.7C672,139,768,181,864,197.3C960,213,1056,203,1152,170.7C1248,139,1344,85,1392,58.7L1440,32L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0L192,0L96,0L0,0Z"></path></svg>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
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
        }
        .social-login .btn:hover {
            background-color: rgba(255,255,255,0.2);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <div class="login-container row g-0">
                    <div class="col-md-5 background-image d-none d-md-flex">
                        <h2><?php echo $config['site']['name']; ?></h2>
                        <p class="mt-auto">Securing Your Digital Future</p>
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
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            <div class="mb-3 d-flex justify-content-between">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberCheck">
                                    <label class="form-check-label text-white" for="rememberCheck">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-white text-decoration-underline">Forgot password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                            
                            <div class="text-center text-white mb-3">
                                Don't have an account? <a href="signup.php" class="text-decoration-underline text-white">Create an account</a>
                            </div>
                            
                            <div class="text-center text-white mb-3">Or login with</div>
                            
                            <div class="social-login d-flex gap-3 justify-content-center">
                                <button type="button" class="btn btn-outline-light d-flex align-items-center gap-2">
                                    <img src="https://www.vectorlogo.zone/logos/google/google-icon.svg" width="20" height="20">
                                    Google
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>