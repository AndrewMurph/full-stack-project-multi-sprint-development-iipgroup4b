<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";
require_once __DIR__ . "/../../classlib/services/PatrolService.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";

// Operations Manager only
authenticate(2);

$data = json_decode(file_get_contents("php://input"), true) ?: [];

$patrolDate = (string)($data["patrolDate"] ?? "");
$description = (string)($data["patrolDescription"] ?? "Regular Scheduled Patrol");

try {
    $svc = new PatrolService($pdo);
    $patrolNr = $svc->createPatrol($patrolDate, $description);

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Patrol created successfully",
        "patrolNr" => $patrolNr
    ]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}