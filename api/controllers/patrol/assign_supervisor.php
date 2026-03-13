<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use POST."]);
    exit;
}

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/guard.php";
require_once __DIR__ . "/../../classlib/services/PatrolService.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";

// Auth (JWT)
$user = require_guard();
$userTypeNr = (int)($user['userTypeNr'] ?? 0);

// Roles
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);

// Only Manager/Admin can assign supervisor
if (!in_array($userTypeNr, [ROLE_MANAGER, ROLE_ADMIN], true)) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Forbidden"]);
    exit;
}

// Input (form or JSON)
$data = $_POST ?: (json_decode(file_get_contents("php://input"), true) ?: []);
$patrolNr    = (int)($data['patrolNr'] ?? 0);
$superUserNr = (int)($data['superUserNr'] ?? 0);

try {
    $svc = new PatrolService($pdo);
    $svc->assignSupervisor($patrolNr, $superUserNr);

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "message" => "Supervisor assigned to patrol",
        "data" => [
            "patrolNr" => $patrolNr,
            "SuperUserNr" => $superUserNr
        ]
    ]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}