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

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

$apiUrl = $protocol . "://" . $_SERVER['HTTP_HOST']
. "/full-stack-project-multi-sprint-development-iipgroup4b/api/controllers/patrol/list_patrols.php";


$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $token]);

$responce = curl_exec($ch);
curl_close($ch);

$result = json_decode($responce, true);

$patrols = [];

if ($result && isset($result['success']) && $result['success'] === true) {
    $patrols = $result['data'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Patrols</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">
   <?php include __DIR__ . "/layout/nav.php"; ?>

<h2>Manage Patrols</h2>

<a href="create_patrol.php" class="btn btn-primary mb-3">Create Patrol</a>
<a href="dashboard.php" class="btn btn-secondary mb-3">Back</a>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Description</th>
            <th>Status</th>
            <th>Supervisor</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php if (!empty($patrols)) : ?>
        <?php foreach ($patrols as $patrol) : ?>
            <tr>
                <td><?= $patrol['patrolNr'] ?></td>
                <td><?= $patrol['patrolDate'] ?></td>
                <td><?= htmlspecialchars($patrol['patrolDescription']) ?></td>
                <?php
                    $status = (int)$patrol['patrol_status'];

                    switch ($status) {
                        case 0:
                            $badge = '<span class="badge bg-warning text-dark">Not Released</span>';
                            break;
                        case 1:
                            $badge = '<span class="badge bg-primary">Released</span>';
                            break;
                        case 2:
                            $badge = '<span class="badge bg-danger">Suspended</span>';
                            break;
                        case 3:
                            $badge = '<span class="badge bg-secondary">Postponed</span>';
                            break;
                        case 4:
                            $badge = '<span class="badge bg-success">Finalised</span>';
                            break;
                        default:
                            $badge = '<span class="badge bg-dark">Unknown</span>';
                    }
                    ?>

                <td><?= $badge ?></td>
                <td><?= $patrol['SuperUserNr'] ?? 'Unassigned' ?></td>
<td>

<a href="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/view_patrol.php?id=<?= $patrol['patrolNr'] ?>" class="btn btn-sm btn-info">View</a>

<a href="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/edit_patrol.php?id=<?= $patrol['patrolNr'] ?>" class="btn btn-sm btn-warning">Edit</a>

<a href="/full-stack-project-multi-sprint-development-iipgroup4b/api/views/assign_supervisor_form.php?id=<?= $patrol['patrolNr'] ?>" class="btn btn-sm btn-secondary">Assign</a>

<?php if ($status === 0): ?>
<a href="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/update_status.php?id=<?= $patrol['patrolNr'] ?>&status=1" class="btn btn-sm btn-primary">Publish</a>
<?php elseif ($status === 1): ?>
<a href="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/update_status.php?id=<?= $patrol['patrolNr'] ?>&status=2" class="btn btn-sm btn-danger">
Suspend
</a>

<?php elseif ($status === 2): ?>
<a href="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/update_status.php?id=<?= $patrol['patrolNr'] ?>&status=1" class="btn btn-sm btn-success">
Re-Release
</a>
<?php endif; ?>

</td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="6" class="text-center">No patrols found</td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>

</body>
</html>