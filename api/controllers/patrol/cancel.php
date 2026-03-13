<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";
require_once __DIR__ . "/../classlib/services/PatrolService.php";
require_once __DIR__ . "/../classlib/baseClasses/ValidationException.php";

authenticate(2); // manager role required

if ($_SERVER["REQUEST_METHOD"] !== "PUT" && $_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$patrolNr = (int)($data["patrolNr"] ?? 0);

try {
    $svc = new PatrolService($pdo);
    $svc->cancelPatrol($patrolNr);

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Patrol cancelled successfully"]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}