<?php
require_once 'config.php';

try {
    $conn = getDbConnection();
    $sql = file_get_contents('add_password_reset_columns.sql');
    $conn->exec($sql);
    echo "Migration successful.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
