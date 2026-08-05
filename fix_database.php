<?php
/**
 * Direct Database Fix Script
 * 
 * This script directly applies the necessary database changes
 * to support Google authentication and login tracking.
 */

// Include configuration file
require_once 'config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Direct Database Fix</h1>";
echo "<p>Applying database changes directly...</p>";

try {
    // Connect to the database
    $conn = getDbConnection();
    echo "<p>✅ Connected to database successfully</p>";
    
    // Check if users table exists
    $stmt = $conn->query("SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'users'
    )");
    $usersTableExists = $stmt->fetchColumn();
    
    if (!$usersTableExists) {
        echo "<p style='color:red;'>❌ Users table does not exist. Please run the signup process first to create the initial database structure.</p>";
    } else {
        echo "<p>✅ Users table exists</p>";
        
        // Check and add columns to users table
        $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $columnsToAdd = [
            'google_id' => "ALTER TABLE users ADD COLUMN IF NOT EXISTS google_id VARCHAR(100) NULL",
            'auth_provider' => "ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(20) DEFAULT 'email'",
            'profile_picture' => "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture TEXT NULL",
            'last_login' => "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login TIMESTAMP WITH TIME ZONE NULL",
            'login_count' => "ALTER TABLE users ADD COLUMN IF NOT EXISTS login_count INTEGER DEFAULT 0"
        ];
        
        foreach ($columnsToAdd as $column => $sql) {
            if (!in_array($column, $columns)) {
                try {
                    $conn->exec($sql);
                    echo "<p>✅ Added $column column to users table</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red;'>❌ Failed to add $column column: " . htmlspecialchars($e->getMessage()) . "</p>";
                    
                    // Try alternate syntax without IF NOT EXISTS for older PostgreSQL versions
                    if (strpos($e->getMessage(), 'syntax error') !== false) {
                        $altSql = str_replace("IF NOT EXISTS ", "", $sql);
                        try {
                            $conn->exec($altSql);
                            echo "<p>✅ Added $column column to users table (using alternate syntax)</p>";
                        } catch (PDOException $e2) {
                            echo "<p style='color:red;'>❌ Failed with alternate syntax too: " . htmlspecialchars($e2->getMessage()) . "</p>";
                        }
                    }
                }
            } else {
                echo "<p>✅ Column $column already exists</p>";
            }
        }
        
        // Update existing null auth_provider values
        try {
            $conn->exec("UPDATE users SET auth_provider = 'email' WHERE google_id IS NULL AND auth_provider IS NULL");
            $conn->exec("UPDATE users SET auth_provider = 'google' WHERE google_id IS NOT NULL AND auth_provider IS NULL");
            echo "<p>✅ Updated auth_provider values for existing users</p>";
        } catch (PDOException $e) {
            echo "<p style='color:red;'>❌ Failed to update auth_provider values: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        // Create login_history table
        $stmt = $conn->query("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'login_history'
        )");
        $loginHistoryExists = $stmt->fetchColumn();
        
        if (!$loginHistoryExists) {
            try {
                $conn->exec("
                    CREATE TABLE login_history (
                        id SERIAL PRIMARY KEY,
                        user_id UUID NOT NULL REFERENCES users(id),
                        login_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                        ip_address VARCHAR(45) NULL,
                        auth_provider VARCHAR(20) NOT NULL,
                        success BOOLEAN DEFAULT TRUE,
                        user_agent TEXT NULL
                    )
                ");
                $conn->exec("CREATE INDEX idx_login_history_user_id ON login_history(user_id)");
                echo "<p>✅ Created login_history table</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red;'>❌ Failed to create login_history table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>✅ Login history table already exists</p>";
        }
        
        // Create signup_events table
        $stmt = $conn->query("SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'signup_events'
        )");
        $signupEventsExists = $stmt->fetchColumn();
        
        if (!$signupEventsExists) {
            try {
                $conn->exec("
                    CREATE TABLE signup_events (
                        id SERIAL PRIMARY KEY,
                        user_id UUID NOT NULL REFERENCES users(id),
                        signup_time TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                        ip_address VARCHAR(45) NULL,
                        auth_provider VARCHAR(20) NOT NULL,
                        referral_source VARCHAR(255) NULL
                    )
                ");
                $conn->exec("CREATE INDEX idx_signup_events_user_id ON signup_events(user_id)");
                echo "<p>✅ Created signup_events table</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red;'>❌ Failed to create signup_events table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>✅ Signup events table already exists</p>";
        }
    }
    
    echo "<h2>Database Fix Complete</h2>";
    echo "<p>Your database should now be properly configured for Google authentication and login tracking.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="login.php" style="display: inline-block; padding: 10px 20px; background-color: #8a2be2; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;">Go to Login Page</a>
    <a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #333; color: white; text-decoration: none; border-radius: 5px;">Return to Homepage</a>
</div> 