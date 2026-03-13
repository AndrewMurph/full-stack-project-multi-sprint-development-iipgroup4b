<?php
session_start();

if (!isset($_SESSION['token'])) {
    header("Location: ../auth/login_form.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php");
    exit;
}

$token = $_SESSION['token'];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
    . "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/view.php?id=" . $id;

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);


if (!$result || !isset($result['success']) || !$result['success']) {
    echo "Patrol not found.";
    exit;
}

$patrol = $result['data'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patrol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h2>Edit Patrol</h2>

<a href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php" class="btn btn-secondary mb-3">Back</a>

<form method="POST" action="edit_patrol_submit.php">

    <input type="hidden" name="patrolNr" value="<?= $patrol['patrolNr'] ?>">

    <div class="mb-3">
        <label class="form-label">Patrol Date</label>
        <input type="date" 
       name="patrolDate"
       class="form-control"
       value="<?= $patrol['patrolDate'] ?>"
       required>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <input type="text" 
       name="patrolDescription"
       class="form-control"
       value="<?= htmlspecialchars($patrol['patrolDescription']) ?>"
       required>
    </div>

    <button class="btn btn-primary">Update Patrol</button>

</form>

</body>
</html>