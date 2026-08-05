<?php
require_once 'config.php';

try {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("UPDATE admins SET email = :email WHERE username = 'admin'");
    $stmt->execute([':email' => 'testingwork102030@gmail.com']);
    
    echo "Admin email updated successfully to testingwork102030@gmail.com\n";
    
} catch (Exception $e) {
    echo "Update failed: " . $e->getMessage() . "\n";
}


