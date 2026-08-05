<?php
// Include configuration file
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$current_password = '';
$new_password = '';
$confirm_password = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match';
    } else {
        // Validate password strength
        $password_errors = validatePassword($new_password);
        if (!empty($password_errors)) {
            $error_message = 'Password does not meet security requirements: ' . implode(', ', $password_errors);
        } else {
            try {
                // Connect to database
                $conn = getDbConnection();
                
                // Get current user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify current password
                if (!$user || !password_verify($current_password, $user['password'])) {
                    $error_message = 'Current password is incorrect';
                } else {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $update_stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                    $update_stmt->bindParam(':password', $hashed_password);
                    $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
                    
                    if ($update_stmt->execute()) {
                        // Password updated successfully
                        $success_message = 'Password updated successfully!';
                        
                        // Remove password update needed flag
                        unset($_SESSION['password_update_needed']);
                        unset($_SESSION['password_strength_message']);
                        
                        // Clear form fields
                        $current_password = '';
                        $new_password = '';
                        $confirm_password = '';
                    } else {
                        $error_message = 'Failed to update password. Please try again.';
                    }
                }
            } catch (PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
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
    <title>Update Password - <?php echo $config['site']['name']; ?></title>
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
        .password-container {
            background-color: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
            max-width: 600px;
            width: 100%;
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
        .password-strength {
            margin-top: 5px;
        }
        .password-requirements {
            margin-top: 15px;
        }
        .password-requirements ul {
            padding-left: 20px;
        }
        .password-requirements li {
            margin-bottom: 5px;
        }
        .text-success {
            color: #4caf50 !important;
        }
        .text-danger {
            color: #f44336 !important;
        }
    </style>
</head>
<body>
    <video id="video-background" autoplay muted loop>
        <source src="log.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="password-container p-4 p-md-5">
                    <h2 class="text-white mb-4">Update Your Password</h2>
                    <p class="text-white-50 mb-4">
                        For enhanced security, please update your password to meet our new cybersecurity standards.
                        Strong passwords are essential for protecting your account from unauthorized access.
                    </p>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success mb-4">
                            <?php echo htmlspecialchars($success_message); ?>
                            <div class="mt-2">
                                <a href="index.php" class="btn btn-sm btn-light">Return to Home</a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger mb-4">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label text-white">Current Password</label>
                            <div class="position-relative">
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;" onclick="togglePassword('current_password')">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label text-white">New Password</label>
                            <div class="position-relative">
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;" onclick="togglePassword('new_password')">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            
                            <!-- Password strength meter -->
                            <div class="password-strength">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-white small">Password Strength:</span>
                                    <span class="text-white small" id="strength-text">Too Weak</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div id="strength-meter" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            
                            <!-- Password requirements -->
                            <div class="password-requirements small text-white-50">
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
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label text-white">Confirm New Password</label>
                            <div class="position-relative">
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;" onclick="togglePassword('confirm_password')">
                                    <i class="fa fa-eye-slash"></i>
                                </span>
                            </div>
                            <div id="password-match" class="small mt-1"></div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <a href="index.php" class="btn btn-outline-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('password-match');
        
        // Password strength elements
        const strengthMeter = document.getElementById('strength-meter');
        const strengthText = document.getElementById('strength-text');
        
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
        
        // Check password strength on input
        if (newPassword) {
            newPassword.addEventListener('input', function() {
                const val = this.value;
                let score = 0;
                
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
                
                // Check password match
                checkPasswordMatch();
            });
        }
        
        // Check if passwords match
        if (confirmPassword) {
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordMatch() {
            if (newPassword && confirmPassword && passwordMatch) {
                if (confirmPassword.value === '') {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'small mt-1';
                } else if (newPassword.value === confirmPassword.value) {
                    passwordMatch.textContent = 'Passwords match';
                    passwordMatch.className = 'small mt-1 text-success';
                } else {
                    passwordMatch.textContent = 'Passwords do not match';
                    passwordMatch.className = 'small mt-1 text-danger';
                }
            }
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
    
    // Toggle password visibility
    function togglePassword(id) {
        const passwordInput = document.getElementById(id);
        const icon = passwordInput.nextElementSibling.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
    </script>
</body>
</html> 