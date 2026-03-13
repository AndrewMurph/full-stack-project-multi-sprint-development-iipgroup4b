<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/login_form.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? (int)$_GET['status'] : null;

if (!$id || $status === null) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php");
    exit;
}

$token = $_SESSION['token'];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/set_patrol_status.php";

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "patrolNr" => $id,
    "patrol_status" => $status
]));

$response = curl_exec($ch);

if ($response === false) {
    die("cURL error: " . curl_error($ch));
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['success']) && $result['success'] === true) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php");
    exit;
}

echo "<pre>";
print_r([
    "httpCode" => $httpCode,
    "rawResponse" => $response,
    "decoded" => $result
]);
echo "</pre>";
exit;