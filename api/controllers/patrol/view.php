<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/auth_middleware.php";

$decoded = authenticate();

$userType = $decoded->data->userTypeNr;

if ($userType != 1 && $userType != 2 && $userType != 3) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

$patrolNr = $_GET['id'] ?? null;

if (!$patrolNr) {
    http_response_code(400);
    echo json_encode(["error" => "Missing patrol ID"]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT patrolNr, patrolDate, patrolDescription, patrol_status, SuperUserNr
        FROM cw_patrol_schedule
        WHERE patrolNr = ?
    ");

    $stmt->execute([$patrolNr]);

    $patrol = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patrol) {
        http_response_code(404);
        echo json_encode(["error" => "Patrol not found"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $patrol
    ]);

} catch (Exception $e) {

    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}