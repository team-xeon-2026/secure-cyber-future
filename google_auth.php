<?php
// Google Authentication Handler
require_once 'config.php';
require_once 'vendor/autoload.php';

use Google\Client;
use Google\Service\Oauth2;

// Error logging for debugging
// Debugging disabled for production
function debug_log($message) {
    // Disabled
}

// Initialize Google Client
function getGoogleClient() {
    global $config;
    
    $client = new Client();
    $client->setClientId($config['google']['client_id']);
    $client->setClientSecret($config['google']['client_secret']);
    $client->setRedirectUri($config['google']['redirect_uri']);
    $client->addScope('email');
    $client->addScope('profile');
    
    debug_log("Google client initialized with redirect URI: " . $config['google']['redirect_uri']);
    return $client;
}

// Helper function to log errors
function logError($message, $error = null) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message;
    if ($error) {
        $log_message .= " - " . $error;
    }
    error_log($log_message);
}

// Handle the initial Google login request
if (isset($_GET['login']) && $_GET['login'] === 'google') {
    debug_log("Google login request initiated");
    
    $client = getGoogleClient();
    $authUrl = $client->createAuthUrl();
    
    // Store the current timestamp in the session to track login attempts
    $_SESSION['google_auth_initiated'] = time();
    
    debug_log("Redirecting to Google auth URL: " . $authUrl);
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
}

// Handle the Google callback
if (isset($_GET['code'])) {
    debug_log("Google callback received with code parameter");
    
    try {
        $client = getGoogleClient();
        debug_log("Attempting to fetch access token with auth code");
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        // Check if there's an error in the token response
        if (isset($token['error'])) {
            debug_log("Error in token response: " . json_encode($token));
            throw new Exception("Error retrieving access token: " . $token['error_description']);
        }
        
        debug_log("Access token obtained successfully");
        $client->setAccessToken($token);
        
        // Get user information
        $google_oauth = new Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        
        $email = $google_account_info->email;
        $name = $google_account_info->name;
        $google_id = $google_account_info->id;
        $picture = $google_account_info->picture ?? null;
        
        debug_log("Google user info retrieved: email=$email, name=$name, google_id=$google_id");
        
        // Connect to database
        $conn = getDbConnection();
        debug_log("Database connection established");
        
        // Check if user exists by email or Google ID
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR google_id = :google_id");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':google_id', $google_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        debug_log("User query executed. User found: " . ($user ? "Yes, ID: ".$user['id'] : "No"));
        
        // Current timestamp for tracking login activity
        $current_time = date('Y-m-d H:i:s');
        
        if ($user) {
            debug_log("Existing user found, updating Google information");
            // User exists, update their Google information
            // For compatibility with Supabase PostgreSQL, we'll update the query
            $updateStmt = $conn->prepare("
                UPDATE users 
                SET 
                    google_id = :google_id,
                    name = COALESCE(:name, name),
                    profile_picture = :picture,
                    auth_provider = 'google',
                    last_login = :last_login,
                    login_count = COALESCE(login_count, 0) + 1
                WHERE id = :id
            ");
            
            $updateStmt->bindParam(':google_id', $google_id);
            $updateStmt->bindParam(':name', $name);
            $updateStmt->bindParam(':picture', $picture);
            $updateStmt->bindParam(':last_login', $current_time);
            $updateStmt->bindParam(':id', $user['id']);
            
            try {
                $updateResult = $updateStmt->execute();
                
                if (!$updateResult) {
                    debug_log("Failed to update user record: " . json_encode($updateStmt->errorInfo()));
                    logError("Failed to update user record", $updateStmt->errorInfo());
                } else {
                    debug_log("User record updated successfully");
                }
            } catch (PDOException $e) {
                debug_log("Database error during user update: " . $e->getMessage());
                logError("Database error during user update", $e->getMessage());
            }
            
            // Login the user
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['auth_provider'] = 'google';
            $_SESSION['profile_picture'] = $picture;
            $_SESSION['login_time'] = time();
            
            debug_log("User session created: " . json_encode(array(
                'user_id' => $user['id'],
                'user_name' => $name,
                'user_email' => $email,
                'auth_provider' => 'google'
            )));
            
            // Store login event in login_history table if it exists
            try {
                $loginHistoryStmt = $conn->prepare("
                    INSERT INTO login_history (user_id, login_time, ip_address, auth_provider, success)
                    VALUES (:user_id, :login_time, :ip_address, 'google', TRUE)
                ");
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $loginHistoryStmt->bindParam(':user_id', $user['id']);
                $loginHistoryStmt->bindParam(':login_time', $current_time);
                $loginHistoryStmt->bindParam(':ip_address', $ip);
                $loginHistoryResult = $loginHistoryStmt->execute();
                
                debug_log("Login history recorded: " . ($loginHistoryResult ? "Success" : "Failed"));
            } catch (Exception $e) {
                // Login history table might not exist, log and continue
                debug_log("Could not record login history: " . $e->getMessage());
                logError("Could not record login history", $e->getMessage());
            }
            
            debug_log("Redirecting to index.php after successful login");
            header('Location: index.php');
            exit;
        } else {
            debug_log("New user, creating account with Google credentials");
            // User doesn't exist, create a new account
            // Generate a random password for the user (they'll never use it directly)
            $random_password = bin2hex(random_bytes(16));
            $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
            
            // Generate a UUID for the user
            $uuid = generateUUID();
            debug_log("Generated UUID for new user: " . $uuid);
            
            // Check if the users table has the required columns before inserting
            try {
                $columnsQuery = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
                $columns = $columnsQuery->fetchAll(PDO::FETCH_COLUMN);
                debug_log("Available columns in users table: " . implode(", ", $columns));
                
                // Prepare the SQL statement based on available columns
                $sql = "INSERT INTO users (id, name, email, password, google_id";
                $values = "VALUES (:id, :name, :email, :password, :google_id";
                
                debug_log("Building SQL for user creation");
                
                // Add optional columns if they exist
                if (in_array('profile_picture', $columns)) {
                    $sql .= ", profile_picture";
                    $values .= ", :picture";
                    debug_log("Adding profile_picture to insert query");
                }
                
                if (in_array('auth_provider', $columns)) {
                    $sql .= ", auth_provider";
                    $values .= ", 'google'";
                    debug_log("Adding auth_provider to insert query");
                }
                
                if (in_array('created_at', $columns)) {
                    $sql .= ", created_at";
                    $values .= ", :created_at";
                    debug_log("Adding created_at to insert query");
                }
                
                if (in_array('updated_at', $columns)) {
                    $sql .= ", updated_at";
                    $values .= ", :updated_at";
                    debug_log("Adding updated_at to insert query");
                }
                
                if (in_array('last_login', $columns)) {
                    $sql .= ", last_login";
                    $values .= ", :last_login";
                    debug_log("Adding last_login to insert query");
                }
                
                if (in_array('login_count', $columns)) {
                    $sql .= ", login_count";
                    $values .= ", 1";
                    debug_log("Adding login_count to insert query");
                }
                
                $sql .= ") " . $values . ")";
                debug_log("Final SQL for user creation: " . $sql);
                
                $insertStmt = $conn->prepare($sql);
                
                // Bind required parameters
                $insertStmt->bindParam(':id', $uuid);
                $insertStmt->bindParam(':name', $name);
                $insertStmt->bindParam(':email', $email);
                $insertStmt->bindParam(':password', $hashed_password);
                $insertStmt->bindParam(':google_id', $google_id);
                debug_log("Bound required parameters");
                
                // Bind optional parameters if they were included
                if (in_array('profile_picture', $columns)) {
                    $insertStmt->bindParam(':picture', $picture);
                    debug_log("Bound profile_picture parameter: " . ($picture ? "Has value" : "NULL"));
                }
                
                if (in_array('created_at', $columns)) {
                    $insertStmt->bindParam(':created_at', $current_time);
                    debug_log("Bound created_at parameter: " . $current_time);
                }
                
                if (in_array('updated_at', $columns)) {
                    $insertStmt->bindParam(':updated_at', $current_time);
                    debug_log("Bound updated_at parameter: " . $current_time);
                }
                
                if (in_array('last_login', $columns)) {
                    $insertStmt->bindParam(':last_login', $current_time);
                    debug_log("Bound last_login parameter: " . $current_time);
                }
                
                // Execute the statement
                debug_log("Executing user insert statement");
                $result = $insertStmt->execute();
                
                if (!$result) {
                    debug_log("Failed to create user account: " . json_encode($insertStmt->errorInfo()));
                    logError("Failed to create user account", $insertStmt->errorInfo());
                    throw new Exception("User creation failed");
                }
                
                debug_log("New user created successfully with ID: " . $uuid);
                
                // Login the user
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $uuid;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['auth_provider'] = 'google';
                $_SESSION['profile_picture'] = $picture;
                $_SESSION['login_time'] = time();
                
                debug_log("Session created for new user");
                
                // Store signup event in signup_events table if it exists
                try {
                    $signupEventStmt = $conn->prepare("
                        INSERT INTO signup_events (user_id, signup_time, ip_address, auth_provider)
                        VALUES (:user_id, :signup_time, :ip_address, 'google')
                    ");
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $signupEventStmt->bindParam(':user_id', $uuid);
                    $signupEventStmt->bindParam(':signup_time', $current_time);
                    $signupEventStmt->bindParam(':ip_address', $ip);
                    $signupResult = $signupEventStmt->execute();
                    
                    debug_log("Signup event recorded: " . ($signupResult ? "Success" : "Failed"));
                } catch (Exception $e) {
                    // Signup events table might not exist, log and continue
                    debug_log("Could not record signup event: " . $e->getMessage());
                    logError("Could not record signup event", $e->getMessage());
                }
                
                debug_log("Redirecting to index.php after successful signup and login");
                header('Location: index.php');
                exit;
                
            } catch (Exception $e) {
                debug_log("Error creating user account: " . $e->getMessage());
                logError("Error creating user account", $e->getMessage());
                $_SESSION['auth_error'] = 'Failed to create user account: ' . $e->getMessage();
                header('Location: login.php');
                exit;
            }
        }
    } catch (Exception $e) {
        // Handle error
        debug_log("Authentication failed: " . $e->getMessage());
        logError("Authentication failed", $e->getMessage());
        $_SESSION['auth_error'] = 'Authentication failed: ' . $e->getMessage();
        header('Location: login.php');
        exit;
    }
}

// If there's an error from Google
if (isset($_GET['error'])) {
    debug_log("Google returned an error: " . $_GET['error']);
    $_SESSION['auth_error'] = 'Google authentication failed: ' . $_GET['error'];
    header('Location: login.php');
    exit;
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

// If we reach here without redirecting, it's likely an error or direct access
echo '<div style="text-align: center; margin-top: 50px;">';
echo '<h2>Google Authentication</h2>';
echo '<p>This page handles Google authentication for Secure Cyber Future.</p>';
echo '<p>If you are seeing this message, you may have accessed this page directly.</p>';
echo '<p><a href="login.php">Return to Login Page</a></p>';
echo '</div>';
?> 
