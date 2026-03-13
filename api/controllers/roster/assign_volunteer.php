<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../middleware/guard.php";
require_once __DIR__ . "/../classlib/services/RosterService.php";
require_once __DIR__ . "/../classlib/baseClasses/ValidationException.php";

$user = require_guard();
$userTypeNr = (int)($user['userTypeNr'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed. Use POST."]);
    exit;
}

$data = $_POST ?: (json_decode(file_get_contents("php://input"), true) ?: []);

$patrolNr    = (int)($data['patrolNr'] ?? 0);
$volunteerId = (int)($data['volunteer_ID_Nr'] ?? 0);

try {
    $svc = new RosterService($pdo);
    $svc->assignVolunteer($patrolNr, $volunteerId, $userTypeNr);

    echo json_encode(["success" => true, "message" => "Volunteer assigned"]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}