<?php
$data = require __DIR__ . "/../controllers/roster/roster_status.php";

$minVolunteers = $data["minVolunteers"];
$rows = $data["rows"] ?? [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Roster Resourcing Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<?php include __DIR__ . "/layout/nav.php"; ?>

<h2>Roster Resourcing Status</h2>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
    <tr>
        <th>Patrol Date</th>
        <th>Description</th>
        <th>Volunteers Assigned</th>
        <th>SUPER Assigned</th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>

    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['patrolDate']) ?></td>
            <td><?= htmlspecialchars($r['patrolDescription']) ?></td>
            <td><?= (int)$r['volunteerCount'] ?></td>
            <td><?= $r['superAssigned'] ? "Yes" : "No" ?></td>
            <td>
                <?php if ($r['status'] === "Sufficiently Resourced"): ?>
                    <span class="text-success fw-bold">Sufficiently Resourced</span>
                <?php else: ?>
                    <span class="text-danger fw-bold">Under-Resourced</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

</body>
</html>