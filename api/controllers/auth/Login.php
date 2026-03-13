<?php

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/baseClasses/ValidationException.php";
require_once __DIR__ . "/../../classlib/services/AuthServices.php";

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If someone opens this in browser, send them back to the login form
    header("Location: ../../views/login_form.php");
    exit;
}

// Detect if request is JSON (Postman) or normal form submit (browser)
$contentType = $_SERVER["CONTENT_TYPE"] ?? "";
$isJson = str_contains($contentType, "application/json");

// Accept either form POST or JSON body
$data = $_POST;
if (empty($data)) {
    $raw = file_get_contents("php://input");
    $json = json_decode($raw, true);
    if (is_array($json)) $data = $json;
}

$email = trim((string)($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

try {
    $auth = new AuthServices($pdo);
    $result = $auth->login($email, $password);
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['token'] = $result['token'] ?? '';
    /**
     * IMPORTANT:
     * We need the user's type from the login result.
     * Adjust these keys if your AuthServices returns them differently.
     */
    $userTypeNr = (int)($result["user"]["userTypeNr"] ?? $result["userTypeNr"] ?? 0);

    // If it's a JSON request (Postman), return JSON (don't redirect)
    if ($isJson) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(200);
        echo json_encode($result);
        exit;
    }

    // Browser form login -> redirect based on role
    if ($userTypeNr === 3) {
        header("Location: ../../views/volunteer_dashboard.php");
        exit;
    }

    if ($userTypeNr === 2) {
        header("Location: ../../views/dashboard.php");
        exit;
    }

    if ($userTypeNr === 4) {
        header("Location: ../../views/roster_status.php");
        exit;
    }

    // Fallback (unknown role)
    header("Location: ../../views/roster_status.php");
    exit;

} catch (ValidationException $e) {
    if ($isJson) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code($e->getHttpCode());
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    }

    // browser fallback: send back to login with message
    header("Location: ../../views/login_form.php?error=" . urlencode($e->getMessage()));
    exit;

} catch (Throwable $e) {
    if ($isJson) {
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(500);
        echo json_encode(["error" => "Server error", "details" => $e->getMessage()]);
        exit;
    }

    header("Location: ../../views/login_form.php?error=" . urlencode("Server error"));
    exit;
}