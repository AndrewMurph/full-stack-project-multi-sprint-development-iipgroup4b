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

// Accept JSON or form POST
$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
if (stripos($contentType, "application/json") !== false) {
    $data = json_decode(file_get_contents("php://input"), true) ?? [];
} else {
    $data = $_POST ?? [];
}

if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

try {
    $auth = new AuthServices($pdo);
    $userNr = $auth->register($data);

    // http_response_code(201);
    // echo json_encode([
    //     "status" => "registered",
    //     "userNr" => $userNr              /* This is used to show the JSON i just changed it so it will redirct to the login page*/
    // ]);
    header("Location: ../../views/login_form.php?registered=1");
exit;
} catch (ValidationException $e) {
    http_response_code($e->getHttpCode());
    echo json_encode(["error" => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error"]);
}