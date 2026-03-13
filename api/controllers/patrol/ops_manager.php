<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/guard_session.php";

require_once __DIR__ . "/../../classlib/services/PatrolService.php";

$user = require_session_guard();
require_session_role([2]);

$svc = new PatrolService($pdo);

$error = "";
$success = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['assign_super'])) {
            $patrolNr = (int)($_POST['patrolNr'] ?? 0);
            $superUserNr = (int)($_POST['superUserNr'] ?? 0);

            $svc->assignSupervisor($patrolNr, $superUserNr);
            $success = "Supervisor assigned.";
        }

        if (isset($_POST['set_status'])) {
            $patrolNr = (int)($_POST['patrolNr'] ?? 0);
            $statusNr = (int)($_POST['patrol_status'] ?? 0);

            $svc->setPatrolStatus($patrolNr, $statusNr);
            $success = "Patrol status updated.";
        }

        if (isset($_POST['assign_vol'])) {
            $patrolNr = (int)($_POST['patrolNr'] ?? 0);
            $volunteerNr = (int)($_POST['volunteer_ID_Nr'] ?? 0);

            $inserted = $svc->assignVolunteer($patrolNr, $volunteerNr);
            $success = $inserted ? "Volunteer assigned." : "Volunteer already assigned to this patrol.";
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    // PRG (prevents duplicate form resubmission)
    $_SESSION['flash_success'] = $success;
    $_SESSION['flash_error'] = $error;

    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/ops_manager.php");
    exit;
}

// Load patrols
$patrols = $svc->listPatrols();

// Flash messages
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

return [
    "patrols" => $patrols,
    "success" => $success,
    "error" => $error,
];