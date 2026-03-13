<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../controllers/middleware/auth_middleware.php";

$decoded = authenticate();

// only admin or operations manager should list users
$userType = $decoded->data->userTypeNr;

if($userType != 1 && $userType != 2){
    http_response_code(403);
    echo json_encode(["error" => "Access denied"]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT UserNr, FirstName, LastName, email, userTypeNr
        FROM cw_user
        WHERE userEnabled = 1
        ORDER BY LastName ASC
    ");

    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);

} catch(Exception $e){

    http_response_code(500);
    echo json_encode([
        "error" => "Server error"
    ]);
}