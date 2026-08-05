<?php
/**
 * Database Schema Update Script for Authentication Tracking
 * 
 * This script adds new fields and tables to the database to better
 * track user authentication, including Google login integration.
 */

// Include configuration file
require_once 'config.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Schema Update for Authentication Tracking</h1>";

try {
    // Connect to the database
    $conn = getDbConnection();
    echo "<p>✅ Connected to database successfully</p>";
    
    // Read the SQL schema update file
    $sql = file_get_contents('update_users_table.sql');
    if (!$sql) {
        throw new Exception("Could not read SQL file");
    }
    echo "<p>✅ SQL file read successfully</p>";
    
    // Split SQL into individual statements
    $statements = preg_split('/;\s*$/m', $sql);
    $totalStatements = count($statements);
    $successCount = 0;
    
    echo "<h2>Executing SQL Statements:</h2>";
    echo "<ul>";
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $conn->exec($statement);
            echo "<li>✅ Success: " . htmlspecialchars(substr($statement, 0, 100)) . "...</li>";
            $successCount++;
        } catch (PDOException $e) {
            echo "<li>❌ Error: " . htmlspecialchars(substr($statement, 0, 100)) . "...<br>";
            echo "<span style='color:red'>Message: " . htmlspecialchars($e->getMessage()) . "</span></li>";
        }
    }
    
    echo "</ul>";
    
    echo "<h2>Summary:</h2>";
    echo "<p>Total statements: $totalStatements</p>";
    echo "<p>Successful: $successCount</p>";
    echo "<p>Failed: " . ($totalStatements - $successCount) . "</p>";
    
    if ($successCount === $totalStatements) {
        echo "<p style='color:green; font-weight:bold;'>✅ All database updates completed successfully!</p>";
    } else {
        echo "<p style='color:orange; font-weight:bold;'>⚠️ Some database updates were not applied. Check the errors above.</p>";
    }
    
    // Verify the updated schema
    echo "<h2>Verifying Schema Updates:</h2>";
    
    // Check users table columns
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>Users Table Columns:</h3>";
    echo "<ul>";
    $requiredColumns = ['id', 'name', 'email', 'password', 'google_id', 'auth_provider', 'profile_picture', 'last_login', 'login_count'];
    
    foreach ($requiredColumns as $column) {
        if (in_array($column, $columns)) {
            echo "<li>✅ Column '$column' exists</li>";
        } else {
            echo "<li>❌ Column '$column' is missing</li>";
        }
    }
    echo "</ul>";
    
    // Check if tables exist
    $requiredTables = ['login_history', 'signup_events'];
    echo "<h3>Required Tables:</h3>";
    echo "<ul>";
    
    foreach ($requiredTables as $table) {
        $stmt = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = '$table')");
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            echo "<li>✅ Table '$table' exists</li>";
        } else {
            echo "<li>❌ Table '$table' is missing</li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<div style="margin-top: 30px; text-align: center;">
    <a href="index.php" style="display: inline-block; padding: 10px 20px; background-color: #8a2be2; color: white; text-decoration: none; border-radius: 5px;">Return to Homepage</a>
</div> 