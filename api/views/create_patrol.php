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


print_r($_POST);
?>

<!DOCTYPE html>

<html>

<head>

    <title>Create Patrol</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="p-4">

    <h2>Create Patrol</h2>

    <a href="manage_patrols.php" class="btn btn-secondary mb-3">Back</a>


<form method="POST" action="/full-stack-project-multi-sprint-development-iipgroup4b/frontend/operations/create_patrol_submit.php">

        <div class="mb-3">

        <label class="form-label">Patrol Date</label>

        <input type="date" name="patrolDate" class="form-control" required>

        </div>

        <div class="mb-3">

        <label class="form-label">Description</label>

        <input type="text" name="patrolDescription"
        class="form-control"
        value="Regular Scheduled Patrol">

        </div>

        <button class="btn btn-primary">Create Patrol</button>

    </form>
</body>

</html>
