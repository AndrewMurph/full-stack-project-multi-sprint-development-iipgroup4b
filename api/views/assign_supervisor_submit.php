<?php
session_start();

if (
    !isset($_SESSION['user']) ||
    !in_array((int)($_SESSION['user']['userTypeNr'] ?? 0), [1, 2], true)
) {
    header("Location: login_form.php");
    exit;
}

$token = $_SESSION['token'] ?? '';

$patrolNr = (int)($_POST['patrolNr'] ?? 0);
$superUserNr = (int)($_POST['superUserNr'] ?? 0);

if ($patrolNr <= 0 || $superUserNr <= 0) {
    header("Location: assign_supervisor.php?id=" . urlencode($patrolNr) . "&error=" . urlencode("Invalid input"));
    exit;
}

$data = [
    "patrolNr" => $patrolNr,
    "superUserNr" => $superUserNr
];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/assign_supervisor.php";

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

if ($result && isset($result['success']) && $result['success'] === true) {
    header("Location: manage_patrols.php?success=" . urlencode("Supervisor assigned successfully"));
    exit;
}

$error = $result['error'] ?? 'Failed to assign supervisor';
header("Location: assign_supervisor.php?id=" . urlencode($patrolNr) . "&error=" . urlencode($error));
exit;