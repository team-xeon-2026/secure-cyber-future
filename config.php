<?php
// Load Dotenv if available
require_once __DIR__ . '/vendor/autoload.php';
if (class_exists(\Dotenv\Dotenv::class) && file_exists(__DIR__ . '/.env')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Dynamically determine the base URL for OAuth redirect
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . $host;
// If the app is in a subfolder like 'xeonnew' or 'xeon' locally, we should try to detect it or use a default.
// In production on Render, the site is at the root.
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isLocalXampp = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
$basePath = $isLocalXampp ? '/xeon' : ''; // Hardcoded local path, empty for production root

// Database configuration for Supabase PostgreSQL connection
$config = [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'aws-1-us-east-1.pooler.supabase.com',
        'port' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '6543',
        'dbname' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'postgres',
        'user' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'postgres.ohgmrgsovsgbrbyuiwfx',
        'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: ''
    ],
    'site' => [
        'name' => 'Secure Cyber Future',
        'tagline' => 'Cyber Security Solutions',
        'founded_year' => 2015
    ],
    // Google OAuth Configuration
    'google' => [
        'client_id' => $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => $_ENV['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => $baseUrl . $basePath . '/index.php'
    ],
    // Google reCAPTCHA Configuration
    'recaptcha' => [
        'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY') ?: '',
        'secret_key' => $_ENV['RECAPTCHA_SECRET_KEY'] ?? getenv('RECAPTCHA_SECRET_KEY') ?: '',
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
        error_log("Database connection failed: " . $e->getMessage());
        die("System is undergoing maintenance. Please try again later.");
    }
}