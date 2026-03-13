<?php

require_once __DIR__ . "/../../libs/jwt_loader.php";
require_once __DIR__ . "/../../config/jwt.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;




function authenticate($requiredRoles = null)
{
    global $LoginKey;

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "Authorization token required"]);
        exit;
    }

    $jwt = $matches[1];

    try {

        $decoded = JWT::decode($jwt, new Key($LoginKey, 'HS256'));

        // Role check (if roles are provided)
        if ($requiredRoles !== null) {

            // Convert single role to array automatically
            $requiredRoles = (array)$requiredRoles;

            if (!in_array($decoded->data->userTypeNr, $requiredRoles)) {
                http_response_code(403);
                echo json_encode(["error" => "Access denied"]);
                exit;
            }
        }

        return $decoded;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or expired token"]);
        exit;
    }
}

