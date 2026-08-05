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
            
            // Check if admin exists
            $stmt = $conn->prepare("SELECT id, username FROM admins WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate token regardless to prevent email enumeration
            if ($admin) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));
                // Set expiration to 1 hour from now
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Update database
                $updateStmt = $conn->prepare("UPDATE admins SET reset_token = :token, reset_token_expires_at = :expires WHERE id = :id");
                $updateStmt->bindParam(':token', $token);
                $updateStmt->bindParam(':expires', $expires);
                $updateStmt->bindParam(':id', $admin['id']);
                $updateStmt->execute();
                
                // Determine base URL dynamically
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $resetLink = $protocol . $host . $basePath . '/admin_reset_password.php?token=' . $token;
                
                // Send email
                $mail = new PHPMailer(true);
                
                $mail->isSMTP();
                $mail->Host       = $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?? 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME');
                $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
                $mail->SMTPSecure = 'tls';
                $mail->Port       = $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?? 587;
                
                $mail->setFrom($_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'), 'Admin System - ' . $config['site']['name']);
                $mail->addAddress($email, $admin['username']);
                
                $mail->isHTML(true);
                $mail->Subject = 'Admin Password Reset - ' . $config['site']['name'];
                $mail->Body    = "
                    <h2>Admin Password Reset Request</h2>
                    <p>Hello {$admin['username']},</p>
                    <p>We received a request to reset your admin portal password. Click the button below to choose a new one:</p>
                    <p><a href='{$resetLink}' style='display:inline-block;padding:10px 20px;background-color:#4a148c;color:#ffffff;text-decoration:none;border-radius:5px;'>Reset Admin Password</a></p>
                    <p>If you did not request this, please ignore this email. This link will expire in 1 hour.</p>
                    <br>
                    <p>Alternatively, you can copy and paste this link into your browser:</p>
                    <p>{$resetLink}</p>
                ";
                
                $mail->send();
            }
            
            $success = "If that email is registered as an admin, we have sent a reset link to it.";
            
        } catch (Exception $e) {
            $error = "An error occurred while processing your request. Please try again later.";
        }
    }
}
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password - <?php echo $config['site']['name']; ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
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
        .success-message {
            color: #00C851;
            margin-bottom: 15px;
            background-color: rgba(0,200,81,0.1);
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Admin Recovery</h2>
            <p>Reset Your Admin Password</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <p class="text-white-50 mb-4 text-center">Enter your admin email address and we'll send you a link to reset your password.</p>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Admin Email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="admin_login.php" class="text-white text-decoration-none" style="opacity: 0.8;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
