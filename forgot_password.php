<?php
// Enable GZIP compression
if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

require_once 'config.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $conn = getDbConnection();
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate token regardless to prevent email enumeration
            if ($user) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));
                // Set expiration to 1 hour from now
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update database
                $updateStmt = $conn->prepare("UPDATE users SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id");
                $updateStmt->bindParam(':token', $token);
                $updateStmt->bindParam(':expires', $expires);
                $updateStmt->bindParam(':id', $user['id']);
                $updateStmt->execute();
                
                // Determine base URL dynamically
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $resetLink = $protocol . $host . $basePath . '/reset_password.php?token=' . $token;
                
                // Send email
                $mail = new PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host       = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
                $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
                $mail->SMTPSecure = 'tls';
                $mail->Port       = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587;
                
                $mail->setFrom($_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'), $config['site']['name']);
                $mail->addAddress($email, $user['name']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request - ' . $config['site']['name'];
                $mail->Body    = "
                    <h2>Password Reset Request</h2>
                    <p>Hello {$user['name']},</p>
                    <p>We received a request to reset your password. Click the button below to choose a new one:</p>
                    <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;background-color:#6a11cb;color:#ffffff;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
                    <p>If you did not request this, please ignore this email. This link will expire in 1 hour.</p>
                    <br>
                    <p>Alternatively, you can copy and paste this link into your browser:</p>
                    <p>{$resetLink}</p>
                ";
                
                $mail->send();
            }
            
            // Always show the same success message to prevent user enumeration
            $success = "If that email is in our database, we have sent a password reset link to it.";
            
        } catch (Exception $e) {
            $error = "An error occurred while processing your request. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo $config['site']['name']; ?></title>
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
                        <h2 class="text-white mb-4">Reset Password</h2>
                        <p class="text-white-50 mb-4">Enter your email address and we'll send you a link to reset your password.</p>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success mb-4">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <input type="email" name="email" class="form-control py-2" placeholder="Email Address" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2">Send Reset Link</button>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center text-white mt-4">
                            Remember your password? <a href="login.php" class="text-decoration-underline text-white">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


