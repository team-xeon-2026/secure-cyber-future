<?php
// Database configuration for Supabase PostgreSQL connection
$config = [
    'db' => [
        'host' => 'aws-1-us-east-1.pooler.supabase.com',
        'port' => '6543',
        'dbname' => 'postgres',
        'user' => 'postgres.ohgmrgsovsgbrbyuiwfx',
        'password' => '#Sahil@xeon'
    ],
    'site' => [
        'name' => 'Secure Cyber Future',
        'tagline' => 'Cyber Security Solutions',
        'founded_year' => 2015
    ],
    // Google OAuth Configuration
    'google' => [
        'client_id' => '365909691431-0ijhuutcujvsflu6b5bd3divscd9cvpt.apps.googleusercontent.com',  // Remove trailing space
        'client_secret' => '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',  // Replace with your client secret
        'redirect_uri' => 'http://localhost/xeon/index.php'  // Reverted to match Google Cloud Console
    ],
    // Google reCAPTCHA Configuration
    'recaptcha' => [
        'site_key' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', // Replace with your actual site key
        'secret_key' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe', // Replace with your actual secret key
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify'
    ]
];

// Start the session only if one doesn't exist already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set the default timezone
date_default_timezone_set('Asia/Kolkata'); // Set the default timezone to Asia/Kolkata
/**
 * Get a database connection
 * @return PDO Database connection
 */
function getDbConnection() {
    global $config;
    
    try {
        $dsn = "pgsql:host={$config['db']['host']};port={$config['db']['port']};dbname={$config['db']['dbname']}";
        $conn = new PDO($dsn, $config['db']['user'], $config['db']['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // In production, you would log this error instead of displaying it
        die("Database connection failed: " . $e->getMessage());
    }
}