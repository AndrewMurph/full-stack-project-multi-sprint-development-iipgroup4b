<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../classlib/services/RosterService.php";

session_start();

$userNr = $_SESSION['user']['UserNr'] ?? 0;
$patrolNr = isset($_POST['patrolNr']) ? (int)$_POST['patrolNr'] : 0;

if ($userNr <= 0) {
    header("Location: ../../views/login_form.php");
    exit;
}

$rosterService = new RosterService($pdo);

try {
    $rosterService->submitAvailability($patrolNr, $userNr);
    header("Location: ../../views/volunteer_availability.php?success=1");
    exit;
} catch (Throwable $e) {
    header("Location: ../../views/volunteer_availability.php?error=" . urlencode($e->getMessage()));
    exit;
}