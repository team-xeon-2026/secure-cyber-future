<?php
// Database configuration for Supabase PostgreSQL connection
$config = [
    'db' => [
        'host' => 'aws-0-ap-south-1.pooler.supabase.com',
        'port' => '6543',
        'dbname' => 'postgres',
        'user' => 'postgres.lfeqxghrsokamgzudqnu',
        'password' => 'Sairj@12345',
    ],
    'site' => [
        'name' => 'ByteRox',
        'tagline' => 'Cyber Security Solutions',
        'founded_year' => 2015
    ]
];

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