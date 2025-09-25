<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

echo json_encode([
    'status' => 'PHP is working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'sqlsrv_loaded' => extension_loaded('sqlsrv'),
    'pdo_sqlsrv_loaded' => extension_loaded('pdo_sqlsrv')
]);
