<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../classlib/services/PatrolService.php";
require_once __DIR__ . "/../classlib/services/RosterService.php";

$patrolId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($patrolId <= 0) {
    die("Invalid patrol ID.");
}

$patrolService = new PatrolService($pdo);
$rosterService = new RosterService($pdo);

$patrol = $patrolService->getById($patrolId);

if (!$patrol) {
    die("Patrol not found.");
}

$memberRows = $rosterService->getVolunteerNamesForPatrol($patrolId);
$memberNames = array_map(function($m) {
    return trim(($m['FirstName'] ?? '') . ' ' . ($m['LastName'] ?? ''));
}, $memberRows);

$coordinatorName = trim(($patrol['SuperFirstName'] ?? '') . ' ' . ($patrol['SuperLastName'] ?? ''));
$hasSupervisor = !empty($patrol['SuperUserNr']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patrol Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f6f7fb; }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            background:#fff;
            border-bottom:1px solid #eef2f7;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav-pills .nav-link {
            border-radius: 12px;
            padding: 10px 14px;
            display:flex;
            gap:10px;
            color:#334155;
        }

        .nav-pills .nav-link.active {
            background:#e7f1ff;
            color:#0d6efd;
        }

        .logo {
            width: 38px;
            height: 38px;
            display: block;
            border-radius: 50%;
            object-fit: cover;
            margin: 0;
        }

        .back-link {
            text-decoration:none;
            color:#334155;
            font-weight:600;
            display:inline-flex;
            gap:8px;
            align-items:center;
            margin-bottom:20px;
        }

        .header-row {
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            gap:16px;
            margin-bottom:20px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
        }

        .subtext {
            color:#64748b;
            margin-top:4px;
        }

        .status-pill {
            display:inline-block;
            padding:6px 12px;
            border-radius:999px;
            background:#e7f1ff;
            color:#0d6efd;
            font-weight:700;
            font-size:0.85rem;
        }

        .card-box {
            background:#fff;
            border:1px solid #e5eaf2;
            border-radius:14px;
            padding:20px;
            height:100%;
        }

        .section-title {
            font-size:1rem;
            font-weight:800;
            margin-bottom:16px;
        }

        .info-block {
            padding:12px 0;
            border-top:1px solid #eef2f7;
        }

        .info-block:first-of-type {
            border-top:0;
            padding-top:0;
        }

        .label {
            font-weight:700;
            color:#334155;
            margin-bottom:4px;
        }

        .muted {
            color:#64748b;
        }

        .text-card {
            background:#fff;
            border:1px solid #e5eaf2;
            border-radius:14px;
            padding:20px;
            margin-top:16px;
        }

        ul.equipment-list {
            margin:0;
            padding-left:20px;
            color:#64748b;
        }

        ul.equipment-list li {
            margin-bottom:8px;
        }

        @media (max-width: 767px) {
            .page {
                padding: 16px;
            }

            .header-row {
                flex-direction:column;
                align-items:flex-start;
            }

            .page-title {
                font-size:1.6rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-md bg-white border-bottom sticky-top">
    <div class="container-fluid px-3 px-md-4">

        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="volunteer_dashboard.php">
            <img src="../img/smalllogo.png" alt="logo" class="logo"/>
            <span>Claddagh Watch</span>
        </a>

        <div class="collapse navbar-collapse d-none d-md-flex justify-content-end">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item"><a class="nav-link" href="volunteer_dashboard.php">📋 Dashboard</a></li>
                <li class="nav-item"><a class="nav-link active" href="volunteer_schedule.php">🗓️ Schedule</a></li>
                <li class="nav-item"><a class="nav-link" href="volunteer_members.php">👥 Members</a></li>
                <li class="nav-item"><a class="nav-link" href="volunteer_availability.php">✅ Availability</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>

    </div>
</nav>

<div class="page">
    <a href="volunteer_schedule.php" class="back-link">← Back to Schedule</a>

    <div class="header-row">
        <div>
            <h1 class="page-title">Patrol Details</h1>
            <div class="subtext"><?= htmlspecialchars($patrol['patrolDescription'] ?? 'Patrol') ?></div>
        </div>
        <span class="status-pill">Scheduled</span>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card-box">
                <div class="section-title">Basic Information</div>

                <div class="info-block">
                    <div class="label">Date</div>
                    <div class="muted"><?= htmlspecialchars($patrol['patrolDate'] ?? '-') ?></div>
                </div>

                <div class="info-block">
                    <div class="label">Time</div>
                    <div class="muted">TBD</div>
                </div>

                <div class="info-block">
                    <div class="label">Location</div>
                    <div class="muted"><?= htmlspecialchars($patrol['patrolDescription'] ?? '-') ?></div>
                </div>

                <div class="info-block">
                    <div class="label">Assigned Volunteers (<?= count($memberNames) ?>)</div>
                    <div class="muted">
                        <?php if (empty($memberNames)): ?>
                            No volunteers assigned
                        <?php else: ?>
                            <?php foreach ($memberNames as $name): ?>
                                <?= htmlspecialchars($name) ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card-box">
                <div class="section-title">Patrol Coordinator</div>

                <div class="info-block">
                    <div class="label">Coordinator</div>
                    <div class="muted"><?= htmlspecialchars($coordinatorName ?: 'Not assigned') ?></div>
                </div>

                <div class="info-block">
                    <div class="label">Supervisor Assigned</div>
                    <div class="muted"><?= $hasSupervisor ? 'Yes' : 'No' ?></div>
                </div>

                <div class="info-block">
                    <div class="label">Patrol Number</div>
                    <div class="muted"><?= (int)$patrolId ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-card">
        <div class="section-title">Description</div>
        <div class="muted">
            <?= htmlspecialchars($patrol['patrolDescription'] ?? 'No description available.') ?>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="text-card">
                <div class="section-title">Required Equipment</div>
                <ul class="equipment-list">
                    <li>Radio</li>
                    <li>Flashlight</li>
                    <li>First Aid Kit</li>
                    <li>Incident Report Form</li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="text-card">
                <div class="section-title">Additional Notes</div>
                <div class="muted">No additional notes.</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>