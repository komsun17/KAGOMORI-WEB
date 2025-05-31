<?php
// get_timesheet.php

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
require_once 'config.php';

$sql = "SELECT * FROM [Fabrication Monitor]";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["error" => sqlsrv_errors()]);
    exit;
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // แปลงข้อมูลวันที่-เวลาให้อ่านง่าย
    foreach ($row as $key => $value) {
        if ($value instanceof DateTime) {
            $row[$key] = $value->format('Y-m-d H:i:s');
        }
    }
    $data[] = $row;
}

echo json_encode($data);
?>
