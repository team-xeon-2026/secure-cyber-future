<?php
// Include configuration file
require_once 'config.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is logged in as admin
 * @return bool True if the user is logged in as admin, false otherwise
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Authenticate an admin user with Supabase
 * @param string $username The username
 * @param string $password The password
 * @return array|bool The admin user details if authentication succeeds, false otherwise
 */
function authenticate_admin($username, $password) {
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, password, role FROM admins WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password'])) {
            return [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'role' => $admin['role']
            ];
        }
    } catch (PDOException $e) {
        error_log("Admin auth error: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Logs in an admin user
 * @param array $admin The admin user details
 */
function admin_login($admin) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
}

/**
 * Logs out an admin user
 */
function admin_logout() {
    // Unset admin session variables
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_role']);
    
    // Redirect to admin login page
    header('Location: admin_login.php');
    exit();
}

/**
 * Check if a user is admin and redirect to login page if not
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        // Save the requested URL for redirection after login
        $_SESSION['admin_redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to admin login page
        header('Location: admin_login.php');
        exit();
    }
} 


