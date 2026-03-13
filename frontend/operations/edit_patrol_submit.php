<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: ../auth/login_form.php");
    exit;
}

$token = $_SESSION['token'];

$data = [
    "patrolNr" => $_POST['patrolNr'] ?? null,
    "patrolDate" => $_POST['patrolDate'] ?? null,
    "patrolDescription" => $_POST['patrolDescription'] ?? null
];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/edit.php";

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $token
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

 if (isset($result['success']) && $result['success'] === true) {
     header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php");
     exit;
 }

 echo "Failed to update patrol";
