<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../../libs/jwt_loader.php";
require_once __DIR__ . "/../../config/jwt.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function get_bearer_token(): ?string
{

    $headers = function_exists('getallheaders') ? getallheaders() : [];

    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;


    if (!$auth && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!$auth && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (!$auth) return null;

    if (preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
        return $m[1];
    }
    return null;
}

/**
 * Require user to be logged in via JWT Bearer token.
 * Returns a normalized user array: UserNr, email, userTypeNr
 */
function require_guard(): array
{
    global $LoginKey;

    $token = get_bearer_token();
    if (!$token) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Missing Bearer token"]);
        exit;
    }

    try {
        $decoded = JWT::decode($token, new Key($LoginKey, 'HS256'));
        $data = (array)($decoded->data ?? []);

        $userNr = (int)($data["UserNr"] ?? 0);
        $email = (string)($data["email"] ?? "");
        $userTypeNr = (int)($data["userTypeNr"] ?? 0);

        if ($userNr <= 0 || $userTypeNr <= 0) {
            http_response_code(401);
            echo json_encode(["success" => false, "error" => "Invalid token payload"]);
            exit;
        }

        return [
            "UserNr" => $userNr,
            "email" => $email,
            "userTypeNr" => $userTypeNr
        ];
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["success" => false, "error" => "Invalid/expired token"]);
        exit;
    }
}

/**
 * Require the current user to have one of the allowed userTypeNr values.
 */
function require_usertypes(array $allowedUserTypeNrs): void
{
    $user = require_guard();
    $userTypeNr = (int)$user['userTypeNr'];

    if (!in_array($userTypeNr, $allowedUserTypeNrs, true)) {
        http_response_code(403);
        echo json_encode(["success" => false, "error" => "Forbidden"]);
        exit;
    }
}