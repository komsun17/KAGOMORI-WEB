<?php
// config.php - Development configuration

$serverName = "192.168.10.80\\MSSQLSERVER19";
$connectionOptions = array(
    "Database" => "NAVTILIV",
    "Uid" => "sa",
    "PWD" => "password",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    $errors = sqlsrv_errors();
    error_log("SQL Server connection failed: " . print_r($errors, true));
    throw new Exception("Database connection failed: " . json_encode($errors));
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
