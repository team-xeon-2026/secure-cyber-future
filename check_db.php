<?php
/**
 * Database Diagnostic Script
 * 
 * This script checks if the database structure is correct and has all the
 * required tables and columns for Google authentication to work properly.
 */

// Include configuration file
require_once 'config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Diagnostic Results</h1>";
echo "<p>Checking if your database structure is configured correctly for Google authentication...</p>";

try {
    // Connect to the database
    $conn = getDbConnection();
    echo "<p style='color:green;'>✅ Connected to database successfully</p>";
    
    // Check if users table exists
    $stmt = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'users'
    )");
    $usersTableExists = $stmt->fetchColumn();
    
    if ($usersTableExists) {
        echo "<p style='color:green;'>✅ Users table exists</p>";
        
        // Check users table columns
        $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h2>Users Table Columns:</h2>";
        echo "<ul>";
        $requiredColumns = [
            'id', 'name', 'email', 'password', 'google_id', 
            'auth_provider', 'profile_picture', 'last_login', 'login_count'
        ];
        
        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columns)) {
                echo "<li style='color:green;'>✅ Column '$column' exists</li>";
            } else {
                echo "<li style='color:red;'>❌ Column '$column' is missing</li>";
                $missingColumns[] = $column;
            }
        }
        echo "</ul>";
        
        if (!empty($missingColumns)) {
            echo "<p style='color:red;'>⚠️ Some required columns are missing. Please run the update_users_table.sql script.</p>";
            
            // Generate alter table statements
            echo "<h3>SQL Fix Commands:</h3>";
            echo "<pre>";
            foreach ($missingColumns as $column) {
                switch ($column) {
                    case 'google_id':
                        echo "ALTER TABLE users ADD COLUMN google_id VARCHAR(100) NULL;\n";
                        break;
                    case 'auth_provider':
                        echo "ALTER TABLE users ADD COLUMN auth_provider VARCHAR(20) DEFAULT 'email';\n";
                        break;
                    case 'profile_picture':
                        echo "ALTER TABLE users ADD COLUMN profile_picture TEXT NULL;\n";
                        break;
                    case 'last_login':
                        echo "ALTER TABLE users ADD COLUMN last_login TIMESTAMP WITH TIME ZONE NULL;\n";
                        break;
                    case 'login_count':
                        echo "ALTER TABLE users ADD COLUMN login_count INTEGER DEFAULT 0;\n";
                        break;
                }
            }
            echo "</pre>";
        }
    } else {
        echo "<p style='color:red;'>❌ Users table does not exist</p>";
    }
    
    // Check if login_history table exists
    $stmt = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'login_history'
    )");
    $loginHistoryExists = $stmt->fetchColumn();
    
    if ($loginHistoryExists) {
        echo "<p style='color:green;'>✅ Login history table exists</p>";
    } else {
        echo "<p style='color:red;'>❌ Login history table does not exist. Please run the update_users_table.sql script.</p>";
        
        // Generate create table statement
        echo "<h3>SQL Fix Command:</h3>";
        echo "<pre>";
        echo "CREATE TABLE IF NOT EXISTS login_history (
    id SERIAL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    login_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    auth_provider VARCHAR(20) NOT NULL,
    success BOOLEAN DEFAULT TRUE,
    user_agent TEXT NULL
);\n";
        echo "</pre>";
    }
    
    // Check if signup_events table exists
    $stmt = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'signup_events'
    )");
    $signupEventsExists = $stmt->fetchColumn();
    
    if ($signupEventsExists) {
        echo "<p style='color:green;'>✅ Signup events table exists</p>";
    } else {
        echo "<p style='color:red;'>❌ Signup events table does not exist. Please run the update_users_table.sql script.</p>";
        
        // Generate create table statement
        echo "<h3>SQL Fix Command:</h3>";
        echo "<pre>";
        echo "CREATE TABLE IF NOT EXISTS signup_events (
    id SERIAL PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id),
    signup_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    auth_provider VARCHAR(20) NOT NULL,
    referral_source VARCHAR(255) NULL
);\n";
        echo "</pre>";
    }
    
    // Create a link to run the database update script
    echo "<div style='margin-top: 20px;'>";
    echo "<a href='run_db_updates.php' style='display: inline-block; padding: 10px 20px; background-color: #8a2be2; color: white; text-decoration: none; border-radius: 5px;'>Run Database Updates</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Add a manual fix button that will apply SQL fixes directly
echo "<h2>Manual Database Fix</h2>";
echo "<p>If the 'Run Database Updates' link doesn't work, you can try this manual fix button:</p>";
echo "<form method='post' action='fix_database.php'>";
echo "<button type='submit' style='padding: 10px 20px; background-color: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;'>Apply Database Fixes Directly</button>";
echo "</form>";

echo "<div style='margin-top: 30px; text-align: center;'>";
echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #333; color: white; text-decoration: none; border-radius: 5px;'>Return to Homepage</a>";
echo "</div>";
?> 