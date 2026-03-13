<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php"; // not strictly needed, but keeps bootstrap style consistent
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

try {
    $auth = new AuthServices($pdo);
    $auth->logout();

    http_response_code(200);
    echo json_encode(["message" => "Logged out"]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}