<?php
// config.php

$serverName = "192.168.10.80\MSSQLSERVER19"; // แก้เป็น IP SQL Server
$connectionOptions = array(
    "Database" => "NAVTILIV", // ชื่อฐานข้อมูล
    "Uid" => "sa",              // แก้ให้เหมาะสม
    "PWD" => "password",       // แก้ให้เหมาะสม
    "CharacterSet" => "UTF-8"
);

// เชื่อมต่อฐานข้อมูล
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>
