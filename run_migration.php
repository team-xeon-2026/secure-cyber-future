<?php
// Run database migration to add google_id column
require_once 'config.php';

try {
    $conn = getDbConnection();
    
    // Execute the SQL migration
    $sql = file_get_contents('add_google_id_migration.sql');
    $result = $conn->exec($sql);
    
    echo "Migration completed successfully!";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
?> 
