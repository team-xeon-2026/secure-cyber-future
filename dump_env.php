<?php
require_once 'vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
var_dump($_ENV);
var_dump(getenv('DB_PASSWORD'));
