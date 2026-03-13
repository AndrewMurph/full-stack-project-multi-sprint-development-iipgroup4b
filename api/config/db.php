<?php


$host = "localhost";
$db   = "cwp_roster";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=3307;dbname=$db;charset=utf8mb4",
        //"mysql:host=$host;port=3306;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} 
catch (PDOException $e) 
{
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}