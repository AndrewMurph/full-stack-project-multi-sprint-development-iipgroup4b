
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
          . "/full-stack-project-multi-sprint-development-iipgroup4b/api/dashboard/summary.php";

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $token
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

?>


<!DOCTYPE html>
<html>
<head>
    <title>Operations Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
   <?php include __DIR__ . "/layout/nav.php"; ?>

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="text-center">Operations</h4>
            <hr>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link text-white" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="manage_patrols.php">Manage Patrols</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="view_all_users.php">View Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../frontend/auth/logout.php">Logout</a>               
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-4">Dashboard Overview</h2>

            <div class="row g-4">

                <!-- Total Patrols -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Total Patrols</h5>
                            <h3><?php echo $data['totalPatrols'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Not Released -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Not Released</h5>
                            <h3><?php echo $data['notReleased'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Released -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Released</h5>
                            <h3><?php echo $data['released'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Finalised -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Finalised</h5>
                            <h3><?php echo $data['finalised'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Active Volunteers -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Active Volunteers</h5>
                            <h3><?php echo $data['activeVolunteers'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5>Total Users</h5>
                            <h3><?php echo $data['totalUsers'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

</body>
</html>