<?php
session_start();

if (
    !isset($_SESSION['user']) ||
    !in_array((int)($_SESSION['user']['userTypeNr'] ?? 0), [1, 2], true)
) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/login_form.php");
        exit;
    exit;
}

$token = $_SESSION['token'] ?? '';

$patrolDate = $_POST['patrolDate'] ?? null;
$description = $_POST['patrolDescription'] ?? null;

$data = [
    "patrolDate" => $patrolDate,
    "patrolDescription" => $description
];

$apiUrl = "http://localhost:8081/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/create.php";

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
echo "<pre>";
print_r($response);
echo "</pre>";
if(isset($result['patrolNr']))
{
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php");
    exit;
}

echo "Failed to create patrol";