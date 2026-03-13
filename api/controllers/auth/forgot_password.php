<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$email = (string)($data["email"] ?? "");

try {
    $auth = new AuthServices($pdo);
    $result = $auth->generatePasswordResetLink($email);

    http_response_code(200);
    echo json_encode($result);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}