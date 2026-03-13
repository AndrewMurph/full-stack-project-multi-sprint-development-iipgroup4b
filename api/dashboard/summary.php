<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../controllers/middleware/auth_middleware.php";

// Admin and Operations can see this dashboard
$decoded = authenticate();

$userType = (int)($decoded->data->userTypeNr ?? 0);

if ($userType !== 1 && $userType !== 2) {
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

try {

    $totalPatrols = $pdo->query("SELECT COUNT(*) FROM cw_patrol_schedule")->fetchColumn();

    $notReleased = $pdo->query("
        SELECT COUNT(*) FROM cw_patrol_schedule
        WHERE patrol_status = 0
    ")->fetchColumn();

    $released = $pdo->query("
        SELECT COUNT(*) FROM cw_patrol_schedule
        WHERE patrol_status = 1
    ")->fetchColumn();

    $suspended = $pdo->query("
        SELECT COUNT(*) FROM cw_patrol_schedule
        WHERE patrol_status = 2
    ")->fetchColumn();

    $finalised = $pdo->query("
        SELECT COUNT(*) FROM cw_patrol_schedule
        WHERE patrol_status = 4
    ")->fetchColumn();

    $totalUsers = $pdo->query("SELECT COUNT(*) FROM cw_user")->fetchColumn();

    $activeVolunteers = $pdo->query("
        SELECT COUNT(*) FROM cw_user
        WHERE userTypeNr = 3 AND userEnabled = 1
    ")->fetchColumn();

    echo json_encode([
        "totalPatrols" => (int)$totalPatrols,
        "notReleased" => (int)$notReleased,
        "released" => (int)$released,
        "suspended" => (int)$suspended,
        "finalised" => (int)$finalised,
        "totalUsers" => (int)$totalUsers,
        "activeVolunteers" => (int)$activeVolunteers
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}