<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

$data = json_decode(file_get_contents("php://input"), true) ?? [];

$token = (string)($data["token"] ?? "");
$newPassword = (string)($data["newPassword"] ?? "");

try {
    $auth = new AuthServices($pdo);
    $auth->resetPasswordWithToken($token, $newPassword);

    http_response_code(200);
    echo json_encode(["message" => "Password reset successful"]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}
<?php require __DIR__ . "/../controllers/auth/reset_password.php";