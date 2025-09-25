<?php
// config.production.php - Production SQL Server configuration

$serverName = "192.168.10.80\\MSSQLSERVER19";
$connectionOptions = array(
    "Database" => "NAVTILIV",
    "Uid" => "sa",
    "PWD" => "password",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true,
    "Encrypt" => true
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    $errors = sqlsrv_errors();
    error_log("Production SQL Server connection failed: " . print_r($errors, true));
    die(json_encode([
        "error" => "Database connection failed",
        "server" => $serverName,
        "database" => "NAVTILIV",
        "details" => "Connection error in production environment"
    ]));
} else {
    error_log("Production SQL Server connected successfully to " . $serverName);
}

// Disable error display in production
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
