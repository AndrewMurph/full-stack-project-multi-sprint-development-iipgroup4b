<?php
$data = require __DIR__ . "/../../api/controllers/patrol/ops_manager.php";

$patrols = $data["patrols"] ?? [];
$success = $data["success"] ?? "";
$error = $data["error"] ?? "";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ops Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include __DIR__ . "/layout/nav.php"; ?>

<div class="container mt-4">

<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="m-0">Operations Manager Dashboard</h2>
  </div>

  <!-- Patrol table -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th style="width:90px;">PatrolNr</th>
              <th style="width:140px;">Date</th>
              <th>Description</th>
              <th style="width:120px;">Volunteers</th>
              <th style="width:90px;">Status</th>
              <th style="width:90px;">Super</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($patrols as $p): ?>
              <tr>
                <td><?= (int)$p['patrolNr'] ?></td>
                <td><?= htmlspecialchars($p['patrolDate']) ?></td>
                <td><?= htmlspecialchars($p['patrolDescription']) ?></td>
                <td><?= (int)$p['volunteerCount'] ?></td>
                <td><?= (int)$p['patrol_status'] ?></td>
                <td><?= $p['SuperUserNr'] ? (int)$p['SuperUserNr'] : 'None' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Actions -->
  <div class="row g-3">
    <!-- Assign Supervisor -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Assign Supervisor</h5>

          <form method="post" class="mt-3">
            <div class="mb-3">
              <label class="form-label">PatrolNr</label>
              <input type="number" name="patrolNr" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">SuperUserNr</label>
              <input type="number" name="superUserNr" class="form-control" required>
            </div>

            <button type="submit" name="assign_super" class="btn btn-primary w-100">Assign</button>
          </form>

        </div>
      </div>
    </div>

    <!-- Set Patrol Status -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Set Patrol Status</h5>
          <p class="text-muted mb-2">0 = Not Released, 1 = Released</p>

          <form method="post" class="mt-3">
            <div class="mb-3">
              <label class="form-label">PatrolNr</label>
              <input type="number" name="patrolNr" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">patrol_status</label>
              <input type="number" name="patrol_status" class="form-control" required>
            </div>

            <button type="submit" name="set_status" class="btn btn-warning w-100">Update</button>
          </form>

        </div>
      </div>
    </div>

    <!-- Assign Volunteer -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Assign Volunteer</h5>

          <form method="post" class="mt-3">
            <div class="mb-3">
              <label class="form-label">PatrolNr</label>
              <input type="number" name="patrolNr" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">volunteer_ID_Nr</label>
              <input type="number" name="volunteer_ID_Nr" class="form-control" required>
            </div>

            <button type="submit" name="assign_vol" class="btn btn-success w-100">Assign</button>
          </form>

        </div>
      </div>
    </div>
  </div>

</div>

</body>
</html>