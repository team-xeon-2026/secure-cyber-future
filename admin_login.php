<?php
// Enable GZIP compression for faster delivery
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

// Include the admin authentication file
require_once 'admin_auth.php';

// Check if an admin user is already logged in
if (is_admin_logged_in()) {
    // Redirect to admin dashboard
    header('Location: admin.php');
    exit();
}

// Initialize error variable
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate form input
    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        // Authenticate admin user
        $admin = authenticate_admin($username, $password);
        
        if ($admin) {
            // Login successful
            admin_login($admin);
            
            // Redirect to admin dashboard or saved URL
            if (isset($_SESSION['admin_redirect_url'])) {
                $redirect_url = $_SESSION['admin_redirect_url'];
                unset($_SESSION['admin_redirect_url']);
                header('Location: ' . $redirect_url);
            } else {
                header('Location: admin.php');
            }
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// Release session lock to speed up loading if another tab is open
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo $config['site']['name']; ?></title>
    <!-- Preconnect to CDNs for faster DNS resolution -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
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
            max-width: 500px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #4a148c, #880e4f);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .login-body {
            padding: 30px;
        }
        .form-control {
            background-color: rgba(255,255,255,0.1);
            border: none;
            color: white;
            margin-bottom: 20px;
        }
        .form-control:focus {
            background-color: rgba(255,255,255,0.2);
            color: white;
            box-shadow: none;
            border-color: #6a11cb;
        }
        .btn-primary {
            background: linear-gradient(135deg, #4a148c, #880e4f);
            border: none;
            width: 100%;
            padding: 10px;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5d1aa3, #9c1458);
        }
        .error-message {
            color: #ff4444;
            margin-bottom: 15px;
            background-color: rgba(255,0,0,0.1);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Secure Access to Admin Dashboard</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="text-end mt-1">
                        <a href="admin_forgot_password.php" class="text-white small text-decoration-none" style="opacity: 0.8;">Forgot Password?</a>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="index.php" class="text-white text-decoration-none">Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html> 


