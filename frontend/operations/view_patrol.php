<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (
    !isset($_SESSION['user']) ||
    !in_array((int)($_SESSION['user']['userTypeNr'] ?? 0), [1, 2], true)
) {
   header("Location: /full-stack-project-multi-sprint-development-iipgroup4b/api/views/login_form.php");
   exit;

}

$token = $_SESSION['token'] ?? '';

$patrolId = $_GET['id'] ?? null;

if(!$patrolId){
    echo "Invalid patrol ID";
    exit;
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
. "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/view.php?id=" . $patrolId;

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $token]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$patrol = $result['data'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
<title>View Patrol</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h2>Patrol Details</h2>

<a href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/manage_patrols.php" class="btn btn-secondary mb-3">Back</a>

<?php if($patrol): ?>

<table class="table table-bordered w-50">

<tr>
<th>Patrol ID</th>
<td><?= $patrol['patrolNr'] ?></td>
</tr>

<tr>
<th>Date</th>
<td><?= $patrol['patrolDate'] ?></td>
</tr>

<tr>
<th>Description</th>
<td><?= htmlspecialchars($patrol['patrolDescription']) ?></td>
</tr>

<tr>
<th>Status</th>

<td>
<?php
$status = (int)$patrol['patrol_status'];

switch ($status) {
    case 0:
        echo '<span class="badge bg-warning text-dark">Not Released</span>';
        break;
    case 1:
        echo '<span class="badge bg-primary">Released</span>';
        break;
    case 2:
        echo '<span class="badge bg-danger">Suspended</span>';
        break;
    case 3:
        echo '<span class="badge bg-secondary">Postponed</span>';
        break;
    case 4:
        echo '<span class="badge bg-success">Finalised</span>';
        break;
    default:
        echo '<span class="badge bg-dark">Unknown</span>';
}
?>
</td>

</tr>

<tr>
<th>Supervisor</th>
<td><?= $patrol['SuperUserNr'] ?? 'Unassigned' ?></td>
</tr>

</table>

<?php else: ?>

<div class="alert alert-danger">
Unable to load patrol details.
</div>

<?php endif; ?>

</body>
</html>