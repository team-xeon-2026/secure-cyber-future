<?php
// Enable GZIP compression
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

require_once 'config.php';

$error = '';
$success = '';
$validToken = false;
$userId = null;
$token = $_GET['token'] ?? '';

// Validate password strength function
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters long.';
    if (!preg_match('/[A-Z]/', $password)) $errors[] = 'Password must include at least one uppercase letter.';
    if (!preg_match('/[a-z]/', $password)) $errors[] = 'Password must include at least one lowercase letter.';
    if (!preg_match('/[0-9]/', $password)) $errors[] = 'Password must include at least one number.';
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = 'Password must include at least one special character.';
    return $errors;
}

if (empty($token)) {
    $error = "Invalid or missing password reset token.";
} else {
    try {
        $conn = getDbConnection();
        // Check if token exists and hasn't expired
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = :token AND reset_token_expires_at > CURRENT_TIMESTAMP");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $validToken = true;
            $userId = $user['id'];
        } else {
            $error = "This password reset link is invalid or has expired. Please request a new one.";
        }
    } catch (PDOException $e) {
        $error = "An error occurred. Please try again later.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password) || empty($confirm_password)) {
        $error = 'Both password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $password_errors = validatePassword($new_password);
        if (!empty($password_errors)) {
            $error = implode(' ', $password_errors);
        } else {
            try {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the password and clear the token
                $updateStmt = $conn->prepare("UPDATE users SET password = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id");
                $updateStmt->bindParam(':password', $hashed_password);
                $updateStmt->bindParam(':id', $userId);
                $updateStmt->execute();
                
                $success = "Your password has been successfully reset. You can now login.";
                $validToken = false; // Hide the form
            } catch (PDOException $e) {
                $error = "Failed to update password. Please try again later.";
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
    <title>Set New Password - <?php echo $config['site']['name']; ?></title>
    <!-- Preconnect to CDNs -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background-color: #0a0118;
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
        .alert {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: none;
        }
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
        }
        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
        }
    </style>
</head>
<body>
    <video id="video-background" autoplay muted loop>
        <source src="log.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <div class="login-container row g-0">
                    <div class="col-md-5 background-image d-none d-md-flex">
                        <img src="lock.gif" alt="Security Lock" class="lock-image">
                        <div class="text-center">
                            <h2><?php echo htmlspecialchars($config['site']['name']); ?></h2>
                            <p>Account Recovery</p>
                        </div>
                    </div>
                    <div class="col-md-7 p-4 p-md-5">
                        <h2 class="text-white mb-4">Set New Password</h2>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success mb-4">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="login.php" class="btn btn-primary px-4 py-2">Go to Login</a>
                            </div>
                        <?php elseif ($validToken): ?>
                            <form method="POST" action="">
                                <div class="mb-3 position-relative">
                                    <input type="password" id="new_password" name="new_password" class="form-control py-2" placeholder="New Password" required>
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 text-white" style="cursor: pointer;" onclick="togglePassword('new_password', this)">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="mb-4 position-relative">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control py-2" placeholder="Confirm Password" required>
                                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 text-white" style="cursor: pointer;" onclick="togglePassword('confirm_password', this)">
                                        <i class="fa fa-eye-slash"></i>
                                    </span>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2">Update Password</button>
                            </form>
                        <?php else: ?>
                            <div class="text-center mt-3">
                                <a href="forgot_password.php" class="btn btn-primary px-4 py-2">Request New Link</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            const icon = iconElement.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>


