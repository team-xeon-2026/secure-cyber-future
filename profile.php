<?php
// Include configuration file
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$user_data = [];

// Fetch user data
try {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        // User not found in database
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    $error_message = 'Database error: ' . $e->getMessage();
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        $error_message = 'Name is required';
    } else {
        try {
            $updateStmt = $conn->prepare("UPDATE users SET name = :name WHERE id = :user_id");
            $updateStmt->bindParam(':name', $name);
            $updateStmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($updateStmt->execute()) {
                // Update session with new name
                $_SESSION['user_name'] = $name;
                $success_message = 'Profile updated successfully!';
                
                // Refresh user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = 'Failed to update profile. Please try again.';
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get login method
$login_method = !empty($user_data['google_id']) ? 'Google' : 'Email';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - <?php echo $config['site']['name']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            background-color: #121212;
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
            color: white;
            padding-top: 80px;
            padding-bottom: 40px;
        }
        
        .profile-container {
            background-color: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            overflow: hidden;
            backdrop-filter: blur(10px);
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
        
        .form-control:disabled {
            background-color: rgba(255,255,255,0.05);
            color: #b0b0cc;
        }
        
        .btn-gradient {
            background: linear-gradient(to right, #8a2be2, #1e90ff);
            color: white;
            border: none;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(to right, #7d27d0, #1c85e8);
            color: white;
        }
        
        .profile-header {
            background: linear-gradient(to right, rgba(138, 43, 226, 0.2), rgba(30, 144, 255, 0.2));
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid rgba(138, 43, 226, 0.3);
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            background-color: #6a11cb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            margin-right: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .login-badge {
            background-color: rgba(138, 43, 226, 0.2);
            color: #8a2be2;
            border: 1px solid rgba(138, 43, 226, 0.3);
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
        }
        
        .google-badge {
            background-color: rgba(219, 68, 55, 0.2);
            color: #db4437;
            border: 1px solid rgba(219, 68, 55, 0.3);
        }
        
        .email-badge {
            background-color: rgba(66, 133, 244, 0.2);
            color: #4285f4;
            border: 1px solid rgba(66, 133, 244, 0.3);
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="profile-container p-4 p-md-5">
                    <h2 class="mb-4">Profile Settings</h2>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success mb-4">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger mb-4">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="profile-header d-flex align-items-center">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <h3 class="mb-1"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h3>
                            <p class="mb-2 text-white-50"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                            <span class="login-badge <?php echo $login_method === 'Google' ? 'google-badge' : 'email-badge'; ?>">
                                <?php if ($login_method === 'Google'): ?>
                                    <i class="fab fa-google me-1"></i> Google Account
                                <?php else: ?>
                                    <i class="fas fa-envelope me-1"></i> Email Account
                                <?php endif; ?>
                            </span>
                            <?php if (!empty($user_data['login_count'])): ?>
                                <span class="badge bg-dark ms-2">
                                    <i class="fas fa-sign-in-alt me-1"></i> 
                                    <?php echo htmlspecialchars($user_data['login_count']); ?> logins
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($user_data['last_login'])): ?>
                                <div class="text-white-50 small mt-2">
                                    <i class="fas fa-clock me-1"></i> Last login: 
                                    <?php echo date('M j, Y g:i A', strtotime($user_data['last_login'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php
                    // Get login history if available
                    $login_history = [];
                    try {
                        $historyStmt = $conn->prepare("
                            SELECT * FROM login_history 
                            WHERE user_id = :user_id 
                            ORDER BY login_time DESC 
                            LIMIT 5
                        ");
                        $historyStmt->bindParam(':user_id', $_SESSION['user_id']);
                        $historyStmt->execute();
                        $login_history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Exception $e) {
                        // Login history table might not exist, continue without error
                    }
                    
                    if (!empty($login_history)): 
                    ?>
                    <div class="card bg-dark mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Login Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Method</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($login_history as $login): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?></td>
                                            <td>
                                                <?php if ($login['auth_provider'] === 'google'): ?>
                                                    <span class="badge bg-danger"><i class="fab fa-google me-1"></i> Google</span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary"><i class="fas fa-envelope me-1"></i> Email</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($login['ip_address']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" disabled>
                            <div class="form-text text-white-50">Email cannot be changed</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <?php if ($login_method === 'Google'): ?>
                                <div class="alert alert-info">
                                    <i class="fab fa-google me-2"></i> You're signed in with Google. Password management is handled by your Google account.
                                </div>
                            <?php else: ?>
                                <a href="update_password.php" class="btn btn-outline-light">
                                    <i class="fas fa-key me-2"></i> Change Password
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Account Security</label>
                            <?php if ($login_method === 'Email'): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-shield-alt me-2"></i> Want to enhance your account security? 
                                    <a href="google_auth.php?login=google" class="text-white">Link your Google account</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-gradient">Save Changes</button>
                            <a href="index.php" class="btn btn-outline-light">Return to Home</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
