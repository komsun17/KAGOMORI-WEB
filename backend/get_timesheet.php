<?php
require_once 'cors.php';

// ==== Content type only; CORS handled by Apache ====
header("Content-Type: application/json; charset=utf-8");

// ==== Handle preflight (fallback if Apache doesn't intercept) ====
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ==== Main logic เริ่มหลังจากนี้เท่านั้น ====
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config.php'; // ตรงนี้จะไม่โดนเรียกถ้าเป็น OPTIONS

    // Fallback define for SQLSRV fetch constants if the extension doesn't expose them
    if (!defined('SQLSRV_FETCH_ASSOC')) {
        define('SQLSRV_FETCH_ASSOC', 2);
    }

    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection not established");
    }

    $sql = "{CALL KAGO_DASHBOARD}";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception("Database query failed: " . json_encode(sqlsrv_errors()));
    }

    $data = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        foreach ($row as $k => $v) {
            if ($v instanceof DateTime) {
                $row[$k] = $v->format('Y-m-d H:i:s');
            }
        }
        $data[] = $row;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage(),
        "timestamp" => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($conn)) sqlsrv_close($conn);
}
