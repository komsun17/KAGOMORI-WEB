<?php
header('Content-Type: application/json; charset=utf-8');
http_response_code(404);

echo json_encode([
    "error" => true,
    "message" => "API endpoint not found",
    "code" => 404,
    "timestamp" => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
