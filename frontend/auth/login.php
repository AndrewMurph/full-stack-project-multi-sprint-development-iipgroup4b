<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_form.php");
    exit;
}

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/auth/Login.php";

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => $email,
    'password' => $password
]));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if ($result && isset($result['token'])) {

    $_SESSION['token'] = $result['token'];
    $_SESSION['userTypeNr'] = $result['user']['userTypeNr'];
    $_SESSION['UserNr'] = $result['user']['UserNr'];

    $role = (int)$result['user']['userTypeNr'];

    if ($role === 1 || $role === 2) {
        header("Location: ../operations/dashboard.php");
        exit;
    }

    if ($role === 3) {
        header("Location: ../volunteer/dashboard.php");
        exit;
    }

    if ($role === 4) {
        header("Location: ../supervisor/dashboard.php");
        exit;
    }

} else {
    $_SESSION['login_error'] = "Invalid email or password";
    header("Location: login_form.php");
    exit;
}