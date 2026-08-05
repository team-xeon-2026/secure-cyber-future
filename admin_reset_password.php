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
$adminId = null;
$token = $_GET['token'] ?? '';

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
        $stmt = $conn->prepare("SELECT id FROM admins WHERE reset_token = :token AND reset_token_expires_at > CURRENT_TIMESTAMP");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            $validToken = true;
            $adminId = $admin['id'];
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
                $updateStmt = $conn->prepare("UPDATE admins SET password = :password, reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id");
                $updateStmt->bindParam(':password', $hashed_password);
                $updateStmt->bindParam(':id', $adminId);
                $updateStmt->execute();
                
                $success = "Your admin password has been successfully reset. You can now login.";
                $validToken = false; // Hide the form
            } catch (PDOException $e) {
                $error = "Failed to update password. Please try again later.";
            }
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
    <title>Admin Set New Password - <?php echo $config['site']['name']; ?></title>
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
            <h2>Admin Reset Password</h2>
            <p>Set a new secure password</p>
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
                <div class="text-center mt-3">
                    <a href="admin_login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php elseif ($validToken): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            <?php else: ?>
                <div class="text-center mt-3">
                    <a href="admin_forgot_password.php" class="text-white" style="opacity:0.8;">Request New Link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
