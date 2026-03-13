<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

$data = json_decode(file_get_contents("php://input"), true) ?? [];

$userNr      = isset($data["userNr"]) ? (int)$data["userNr"] : 0;
$oldPassword = (string)($data["oldPassword"] ?? "");
$newPassword = (string)($data["newPassword"] ?? "");

if ($userNr <= 0 || $oldPassword === "" || $newPassword === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $auth = new AuthServices($pdo);
    $auth->changePassword($userNr, $oldPassword, $newPassword);

    http_response_code(200);
    echo json_encode(["success" => "Password updated successfully"]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}