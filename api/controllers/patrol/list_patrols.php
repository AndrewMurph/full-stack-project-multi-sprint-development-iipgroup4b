<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/guard.php";
require_once __DIR__ . "/../../classlib/services/PatrolService.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";

$user = require_guard();
$userTypeNr = (int)($user["userTypeNr"] ?? 0);

try {
    $svc = new PatrolService($pdo);
    $rows = $svc->listPatrolsForUserType($userTypeNr);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "data" => $rows
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}