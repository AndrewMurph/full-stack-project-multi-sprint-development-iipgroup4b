<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/guard_session.php";
require_once __DIR__ . "/../../classlib/services/UserService.php";

// Managers only
//require_session_role([2]);

try {
    $svc = new UserService($pdo);
    $users = $svc->listUsers();

    http_response_code(200);
    echo json_encode(["users" => $users]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}