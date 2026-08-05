<?php
require_once 'config.php';

try {
    $conn = getDbConnection();
    
    // Create the admins table
    $sql = "
    CREATE TABLE IF NOT EXISTS admins (
        id SERIAL PRIMARY KEY,
        username VARCHAR(255) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50) DEFAULT 'admin',
        reset_token VARCHAR(255) NULL,
        reset_token_expires_at TIMESTAMP WITH TIME ZONE NULL,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
    );
    CREATE INDEX IF NOT EXISTS idx_admins_reset_token ON admins(reset_token);
    ";
    
    $conn->exec($sql);
    echo "Table created successfully.\n";
    
    // Check if admin exists
    $stmt = $conn->query("SELECT COUNT(*) FROM admins");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $hashed_password = password_hash('admin', PASSWORD_DEFAULT);
        
        $insert = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (:username, :email, :password)");
        $insert->execute([
            ':username' => 'admin',
            ':email' => 'leomanthan10@gmail.com', // Default admin email from mail.php
            ':password' => $hashed_password
        ]);
        echo "Default admin created successfully.\n";
    } else {
        echo "Admins already exist, skipping insert.\n";
    }
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}


