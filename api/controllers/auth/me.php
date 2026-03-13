<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

try {
    $auth = new AuthServices($pdo);
    $user = $auth->getSessionUser();

    http_response_code(200);
    echo json_encode(["user" => $user]);
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}