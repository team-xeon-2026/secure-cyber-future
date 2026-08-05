<?php
// Database Connection Test Script

// Include configuration file
require_once 'config.php';

echo "<h1>Supabase Database Connection Test</h1>";
echo "<p>This script will test your connection to Supabase and check database structure.</p>";

try {
    // Attempt to connect to the database
    echo "<h2>1. Testing Database Connection</h2>";
    $conn = getDbConnection();
    echo "<p style='color:green'>✅ Connection successful!</p>";
    
    // Display connection info
    echo "<h2>2. Connection Details</h2>";
    echo "<ul>";
    echo "<li>Host: " . htmlspecialchars($config['db']['host']) . "</li>";
    echo "<li>Database: " . htmlspecialchars($config['db']['dbname']) . "</li>";
    echo "<li>User: " . htmlspecialchars($config['db']['user']) . "</li>";
    echo "</ul>";
    
    // Check if the users table exists
    echo "<h2>3. Checking for Users Table</h2>";
    $checkTableSql = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'users'
    )";
    $tableExists = $conn->query($checkTableSql)->fetchColumn();
    
    if ($tableExists) {
        echo "<p style='color:green'>✅ The 'users' table exists!</p>";
        
        // Check the structure of the users table
        echo "<h2>4. Checking Users Table Structure</h2>";
        $tableStructureSql = "
            SELECT column_name, data_type, is_nullable 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = 'users'
            ORDER BY ordinal_position
        ";
        $tableStructure = $conn->query($tableStructureSql)->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
        echo "<tr><th>Column Name</th><th>Data Type</th><th>Nullable</th></tr>";
        
        foreach ($tableStructure as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($column['data_type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['is_nullable']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Count users in the table
        $countSql = "SELECT COUNT(*) FROM users";
        $userCount = $conn->query($countSql)->fetchColumn();
        
        echo "<p>There are currently <strong>" . $userCount . "</strong> users in the database.</p>";
        
        if ($userCount > 0) {
            // Show limited user data for testing
            echo "<h2>5. Recent Users (First 5 - Partial Data)</h2>";
            $usersSql = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5";
            $users = $conn->query($usersSql)->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse'>";
            echo "<tr><th>UUID</th><th>Name</th><th>Email</th><th>Created At</th></tr>";
            
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } else {
        echo "<p style='color:orange'>⚠️ The 'users' table does not exist yet.</p>";
        echo "<p>When you register the first user, the table will be automatically created.</p>";
        
        // Create users table
        echo "<h2>4. Creating Users Table</h2>";
        echo "<p>Would you like to create the users table now?</p>";
        echo "<form method='post'>";
        echo "<input type='submit' name='create_table' value='Create Users Table'>";
        echo "</form>";
        
        if (isset($_POST['create_table'])) {
            try {
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
                
                echo "<p style='color:green'>✅ Users table created successfully!</p>";
                echo "<p>Please refresh this page to see the table structure.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Error creating table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Error code: " . $e->getCode() . "</p>";
    
    // Provide troubleshooting tips
    echo "<h2>Troubleshooting Tips</h2>";
    echo "<ul>";
    echo "<li>Check if your Supabase host, port, database name, username, and password are correct in config.php</li>";
    echo "<li>Ensure your IP address is allowed in Supabase (check Network Access settings)</li>";
    echo "<li>Verify that your database exists and is accessible</li>";
    echo "<li>Make sure PHP PDO and pgsql extensions are enabled on your server</li>";
    echo "</ul>";
}

// Link back to the registration page
echo "<p><a href='signup.php'>Go to Signup Page</a> | <a href='login.php'>Go to Login Page</a></p>";
?> 