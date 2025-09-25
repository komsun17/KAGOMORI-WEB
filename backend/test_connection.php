<?php
// test_connection.php - Production server diagnostics

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

// Ensure no HTML output
ob_start();

$response = array(
    "server_info" => array(
        "php_version" => phpversion(),
        "server_software" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        "document_root" => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        "script_name" => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
        "request_uri" => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        "server_name" => $_SERVER['SERVER_NAME'] ?? 'Unknown'
    ),
    "timestamp" => date('Y-m-d H:i:s'),
    "status" => "PHP is working"
);

// Check if this is production server
if (strpos($_SERVER['SERVER_NAME'] ?? '', 'thaisinto.com') !== false) {
    $response["environment"] = "Production Server";
    $response["server_info"]["is_production"] = true;
} else {
    $response["environment"] = "Development/Other";
    $response["server_info"]["is_production"] = false;
}

// Test SQL Server connection if config exists
if (file_exists('config.php')) {
    $response["config_file"] = "Found";

    // Test database connection
    try {
        require_once 'config.php';

        if (isset($conn) && $conn) {
            $response["database_connection"] = "Success";

            // Test stored procedure
            $sql = "{CALL KAGO_DASHBOARD}";
            $stmt = sqlsrv_query($conn, $sql);

            if ($stmt !== false) {
                $response["stored_procedure"] = "Success";
                $row_count = 0;
                while (sqlsrv_fetch($stmt)) {
                    $row_count++;
                    if ($row_count >= 5) break; // Count first 5 rows only
                }
                $response["sample_data_count"] = $row_count;
            } else {
                $response["stored_procedure"] = "Failed";
                $response["sp_error"] = sqlsrv_errors();
            }
        } else {
            $response["database_connection"] = "Failed - Connection not established";
        }
    } catch (Exception $e) {
        $response["database_connection"] = "Failed";
        $response["db_error"] = $e->getMessage();
    }
} else {
    $response["config_file"] = "Not found";
    $response["note"] = "config.php file is missing";
}

// Clean any buffered output and return JSON
ob_end_clean();
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
