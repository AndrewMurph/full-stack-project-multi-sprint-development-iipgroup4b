<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";
require_once __DIR__ . "/../../classlib/services/PatrolService.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";

// authenticate any logged-in user
$decoded = authenticate();

$userType = (int)($decoded->data->userTypeNr ?? 0);

// Allowed: Volunteer(3), Super(4), Manager(2)
if (!in_array($userType, [2, 3, 4], true)) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Access denied"]);
    exit;
}

try {
    $svc = new PatrolService($pdo);
    $patrols = $svc->listPublishedFuturePatrols();

    http_response_code(200);
    echo json_encode([
        "success" => true,
        "patrols" => $patrols
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Server error"]);
}