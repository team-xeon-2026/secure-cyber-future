<?php
// Include configuration file
require_once 'config.php';

// Initialize variables for form handling
$registration_success = false;
$error_message = '';
$debug_info = '';  // For debugging

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
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long';
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
                
                // Prepare and execute the query to insert the new user with named parameters
                $stmt = $conn->prepare("
                    INSERT INTO users (id, name, email, password) 
                    VALUES (:id, :name, :email, :password)
                ");
                
                // Bind parameters
                $stmt->bindParam(':id', $uuid);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                
                // Execute the query
                $result = $stmt->execute();
                
                if ($result) {
                    // Registration successful
                    $registration_success = true;
                } else {
                    $error_message = "Failed to insert user record";
                    $debug_info = print_r($stmt->errorInfo(), true);
                }
            }
        } catch (PDOException $e) {
            $error_message = "Registration failed: " . $e->getMessage();
            $debug_info = "Error code: " . $e->getCode();
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account - <?php echo $config['site']['name']; ?></title>
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
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <div class="login-container row g-0">
                    <div class="col-md-5 background-image d-none d-md-flex">
                        <h2><?php echo $config['site']['name']; ?></h2>
                        <p class="mt-auto">Securing Your Digital Future</p>
                    </div>
                    <div class="col-md-7 p-4 p-md-5">
                        <h2 class="text-white mb-4">Create an account</h2>
                        
                        <?php if ($registration_success): ?>
                            <div class="alert alert-success mb-4">
                                Registration successful! <a href="login.php" class="text-white text-decoration-underline">Click here to login</a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger mb-4">
                                <?php echo htmlspecialchars($error_message); ?>
                                <?php if (!empty($debug_info) && isset($_GET['debug'])): ?>
                                    <div class="debug-info"><?php echo htmlspecialchars($debug_info); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <input type="text" name="name" class="form-control" placeholder="Enter Your Name" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="mb-3 position-relative">
                                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                                <span class="position-absolute end-0 top-50 translate-middle-y me-3" style="color: white; cursor: pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
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
                            
                            <div class="text-center text-white mb-3">Or register with</div>
                            
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