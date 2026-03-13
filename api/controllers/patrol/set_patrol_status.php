<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";
require_once __DIR__ . "/../../classlib/services/PatrolService.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use POST."]);
    exit;
}

$decoded = authenticate();

$userType = (int)($decoded->data->userTypeNr ?? 0);

if ($userType !== 1 && $userType !== 2) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "Forbidden"
    ]);
    exit;
}

$data = $_POST ?: (json_decode(file_get_contents("php://input"), true) ?: []);

$patrolNr  = (int)($data['patrolNr'] ?? 0);
$newStatus = (int)($data['patrol_status'] ?? -1);

try {
    $svc = new PatrolService($pdo);
    $result = $svc->setPatrolStatus($patrolNr, $newStatus);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Patrol status updated",
        "data" => $result
    ]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Server error"
    ]);
}