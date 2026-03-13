<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: ../auth/login_form.php");
    exit;
}

$token = $_SESSION['token'];

$patrolId = $_GET['id'] ?? null;

if (!$patrolId) {
    echo "Invalid patrol ID";
    exit;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/users/list_users.php";

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$users = $result['data'] ?? [];

// Filter supervisors only
$supervisors = array_filter($users, function ($user) {
    return $user['userTypeNr'] == 4;
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Supervisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4 bg-light">

<div class="container">

    <div class="card shadow-sm">
        <div class="card-body">

            <h3 class="mb-4">Assign Supervisor</h3>

            <form method="POST" action="assign_supervisor_submit.php">

                <input type="hidden" name="patrolNr" value="<?= $patrolId ?>">

                <div class="mb-3">
                    <label class="form-label">Select Supervisor</label>

                    <select name="superUserNr" class="form-select" required>
                        <option value="">Choose Supervisor</option>

                        <?php foreach ($supervisors as $super): ?>
                            <option value="<?= $super['UserNr'] ?>">
                                <?= htmlspecialchars($super['FirstName'] . ' ' . $super['LastName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Assign Supervisor
                    </button>

                   <a href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>

